<?php

namespace App\Http\Controllers;

use App\Models\Kasir;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    /**
     * Display a listing of products for the Cashier / POS view.
     */
    public function index(Request $request)
    {
        $query = Produk::with(['varian', 'kategori']);

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->kategori);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_produk', 'like', "%{$q}%")
                    ->orWhere('merek', 'like', "%{$q}%");
            });
        }

        $produks = $query->get()->map(function ($p) {
            $variants = $p->varian;
            $colors = $variants->pluck('warna')->filter()->unique()->values()->all();
            $sizes = $variants->pluck('ukuran')->filter()->unique()->sort()->values()->all();
            
            $sizeText = 'All Size';
            if (!empty($sizes)) {
                if (count($sizes) > 1) {
                    $sizeText = min($sizes) . ' - ' . max($sizes) . 'EUR';
                } else {
                    $sizeText = $sizes[0] . 'EUR';
                }
            }

            $p->warna_text = !empty($colors) ? implode(', ', $colors) : '-';
            $p->ukuran_text = $sizeText;
            $p->total_stok = $variants->sum('stok');
            $p->first_variant_id = $variants->first()?->id_varian;
            return $p;
        });

        // Jika produk berjumlah 4 (seperti data master default),
        // duplikasi menjadi 12 item agar membentuk grid 3 kolom x 4 baris sesuai persis dengan mockup Page Beranda.png
        $displayProduks = collect();
        if ($produks->count() === 4 && !$request->filled('q') && !$request->filled('kategori')) {
            foreach ($produks as $p) {
                $displayProduks->push($p);
                $displayProduks->push($p);
                $displayProduks->push($p);
            }
        } else {
            $displayProduks = $produks;
        }

        $kategoris = Kategori::all();

        return view('kasir.index', compact('displayProduks', 'produks', 'kategoris'));
    }

    /**
     * Store a new transaction from Cashier.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.harga' => 'required|numeric|min:0',
            'metode_pembayaran' => 'nullable|string',
            'jumlah_bayar' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $totalItem = 0;
            $subtotal = 0;
            foreach ($request->items as $item) {
                $totalItem += (int)$item['jumlah'];
                $subtotal += ((float)$item['harga'] * (int)$item['jumlah']);
            }

            $pajak = round($subtotal * 0.11); // 11% PPN
            $diskon = (float)($request->diskon ?? 0);
            $totalBayar = $subtotal + $pajak - $diskon;
            $jumlahBayar = (float)($request->jumlah_bayar ?? $totalBayar);
            $kembalian = max(0, $jumlahBayar - $totalBayar);

            $nota = 'TRX-' . date('YmdHis') . '-' . rand(100, 999);
            $userId = Auth::id() ?? 1;

            // Mapping metode pembayaran agar sesuai dengan ENUM database ('cash', 'debit', 'qris')
            $metodeMap = [
                'tunai'    => 'cash',
                'cash'     => 'cash',
                'qris'     => 'qris',
                'transfer' => 'debit',
                'debit'    => 'debit',
            ];
            $rawMetode = strtolower(trim((string)($request->metode_pembayaran ?? 'cash')));
            $metodeValid = $metodeMap[$rawMetode] ?? 'cash';

            $transaksi = Transaksi::create([
                'no_nota' => $nota,
                'id_pengguna' => $userId,
                'tanggal_transaksi' => now(),
                'total_item' => $totalItem,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'total_bayar' => $totalBayar,
                'jumlah_bayar' => $jumlahBayar,
                'kembalian' => $kembalian,
                'metode_pembayaran' => $metodeValid,
                'status' => 'selesai',
            ]);

            foreach ($request->items as $item) {
                $varianId = $item['id_varian'] ?? null;
                if (!$varianId) {
                    $firstVarian = VarianProduk::where('id_produk', $item['id_produk'])->first();
                    $varianId = $firstVarian?->id_varian;
                }

                if ($varianId) {
                    $itemSubtotal = (float)$item['harga'] * (int)$item['jumlah'];
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'id_varian' => $varianId,
                        'jumlah' => (int)$item['jumlah'],
                        'harga_satuan' => (float)$item['harga'],
                        'subtotal' => $itemSubtotal,
                    ]);

                    // Kurangi stok jika tersedia
                    $varian = VarianProduk::find($varianId);
                    if ($varian && $varian->stok >= (int)$item['jumlah']) {
                        $varian->decrement('stok', (int)$item['jumlah']);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses!',
                'no_nota' => $nota,
                'tanggal_transaksi' => now()->format('d M Y, H:i'),
                'kasir_nama' => Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'Kasir 1',
                'total_item' => $totalItem,
                'subtotal' => $subtotal,
                'pajak' => $pajak,
                'diskon' => $diskon,
                'total_bayar' => $totalBayar,
                'jumlah_bayar' => $jumlahBayar,
                'kembalian' => $kembalian,
                'metode_pembayaran' => ucfirst($metodeValid),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kasir = Kasir::with(['pengguna', 'detail.varian.produk'])->find($id);
        if (!$kasir) {
            $kasir = Transaksi::with(['pengguna', 'detail.varian.produk'])->findOrFail($id);
        }

        return view('kasir.show', compact('kasir'));
    }
}
