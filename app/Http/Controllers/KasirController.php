<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
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
        // Hanya produk aktif yang boleh dijual di kasir
        $query = Produk::with(['varian', 'kategori'])->where('status', 'aktif');

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

        $produks = $query->orderBy('nama_produk')->get()->map(function ($p) {
            $variants = $p->varian->sortBy(fn ($v) => [(float) $v->ukuran, $v->warna])->values();
            $colors = $variants->pluck('warna')->filter()->unique()->values()->all();
            $sizes = $variants->pluck('ukuran')->filter()->unique()->sort()->values()->all();

            $sizeText = 'All Size';
            if (!empty($sizes)) {
                $sizeText = count($sizes) > 1 ? min($sizes) . ' - ' . max($sizes) . ' EUR' : $sizes[0] . ' EUR';
            }

            $p->warna_text = !empty($colors) ? implode(', ', $colors) : '-';
            $p->ukuran_text = $sizeText;
            $p->total_stok = $variants->sum('stok');
            // Data untuk popup pilih ukuran
            $p->varian_json = $variants->map(fn ($v) => [
                'id'     => $v->id_varian,
                'ukuran' => $v->ukuran,
                'warna'  => $v->warna,
                'stok'   => (int) $v->stok,
                'sku'    => $v->sku,
            ])->values()->all();
            return $p;
        });

        $displayProduks = $produks;

        $kategoris = Kategori::all();

        return view('kasir.index', compact('displayProduks', 'produks', 'kategoris'));
    }

    /**
     * Simpan transaksi dari halaman Kasir.
     *
     * Aturan keamanan:
     * - Harga SELALU diambil dari database (harga_jual), bukan dari browser.
     * - Stok dicek & dikunci (lockForUpdate) di dalam DB transaction, sehingga dua kasir
     *   tidak bisa menjual pasangan terakhir yang sama. Stok kurang => transaksi ditolak.
     * - Diskon tidak boleh negatif / melebihi subtotal. Tanpa PPN.
     * - Pembayaran tunai harus >= total; debit/QRIS dicatat sebesar total.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'                => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id_varian'    => ['required', 'integer', 'exists:tbl_varian_produk,id_varian'],
            'items.*.jumlah'       => ['required', 'integer', 'min:1', 'max:1000'],
            'metode_pembayaran'    => ['required', 'string', 'in:tunai,cash,debit,transfer,qris,Tunai,Cash,Debit,Transfer,QRIS,Qris'],
            'jumlah_bayar'         => ['nullable', 'numeric', 'min:0'],
            'diskon'               => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.required'             => 'Keranjang masih kosong.',
            'items.*.id_varian.required' => 'Pilih ukuran sepatu terlebih dahulu.',
            'items.*.id_varian.exists'   => 'Varian produk tidak ditemukan (mungkin sudah dihapus).',
            'items.*.jumlah.min'         => 'Jumlah minimal 1 pasang.',
            'metode_pembayaran.in'       => 'Metode pembayaran tidak dikenal.',
            'diskon.min'                 => 'Diskon tidak boleh negatif.',
        ]);

        $metode = match (strtolower($validated['metode_pembayaran'])) {
            'tunai', 'cash'      => 'cash',
            'debit', 'transfer'  => 'debit',
            default              => 'qris',
        };

        // Gabungkan item dengan varian yang sama (mis. diklik 2x dari kartu berbeda)
        $qtyPerVarian = [];
        foreach ($validated['items'] as $item) {
            $id = (int) $item['id_varian'];
            $qtyPerVarian[$id] = ($qtyPerVarian[$id] ?? 0) + (int) $item['jumlah'];
        }

        try {
            $result = DB::transaction(function () use ($qtyPerVarian, $validated, $metode) {
                $varians = VarianProduk::with('produk')
                    ->whereIn('id_varian', array_keys($qtyPerVarian))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id_varian');

                $subtotal = 0;
                $totalItem = 0;
                $lines = [];
                foreach ($qtyPerVarian as $idVarian => $qty) {
                    $varian = $varians[$idVarian];
                    $produk = $varian->produk;

                    if (! $produk || $produk->status !== 'aktif') {
                        throw new CheckoutException("Produk {$produk?->nama_produk} sedang tidak dijual.");
                    }
                    if ($varian->stok < $qty) {
                        throw new CheckoutException(
                            "Stok {$produk->nama_produk} ukuran {$varian->ukuran} ({$varian->warna}) tidak cukup: tersisa {$varian->stok}, diminta {$qty}."
                        );
                    }

                    $harga = (float) $produk->harga_jual; // harga resmi dari database
                    $lines[] = [$varian, $qty, $harga];
                    $subtotal += $harga * $qty;
                    $totalItem += $qty;
                }

                $diskon = (float) ($validated['diskon'] ?? 0);
                if ($diskon > $subtotal) {
                    throw new CheckoutException('Diskon tidak boleh melebihi subtotal.');
                }
                $totalBayar = $subtotal - $diskon;

                if ($metode === 'cash') {
                    $jumlahBayar = (float) ($validated['jumlah_bayar'] ?? 0);
                    if ($jumlahBayar < $totalBayar) {
                        throw new CheckoutException('Jumlah uang diterima kurang dari total tagihan.');
                    }
                } else {
                    $jumlahBayar = $totalBayar; // debit / QRIS dibayar pas lewat mesin EDC/QRIS
                }

                $transaksi = Transaksi::create([
                    'no_nota'           => $this->generateNoNota(),
                    'id_pengguna'       => Auth::id(),
                    'tanggal_transaksi' => now(),
                    'total_item'        => $totalItem,
                    'subtotal'          => $subtotal,
                    'diskon'            => $diskon,
                    'total_bayar'       => $totalBayar,
                    'jumlah_bayar'      => $jumlahBayar,
                    'kembalian'         => $jumlahBayar - $totalBayar,
                    'metode_pembayaran' => $metode,
                    'status'            => 'selesai',
                ]);

                $items = [];
                foreach ($lines as [$varian, $qty, $harga]) {
                    DetailTransaksi::create([
                        'id_transaksi' => $transaksi->id_transaksi,
                        'id_varian'    => $varian->id_varian,
                        'jumlah'       => $qty,
                        'harga_satuan' => $harga,
                        'subtotal'     => $harga * $qty,
                    ]);
                    $varian->decrement('stok', $qty);

                    $items[] = [
                        'id_varian' => $varian->id_varian,
                        'nama'      => $varian->produk->nama_produk,
                        'ukuran'    => $varian->ukuran,
                        'warna'     => $varian->warna,
                        'sku'       => $varian->sku,
                        'jumlah'    => $qty,
                        'harga'     => $harga,
                        'subtotal'  => $harga * $qty,
                        'sisa_stok' => $varian->stok,
                    ];
                }

                return [$transaksi, $items];
            });
        } catch (CheckoutException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            // Detail error hanya ke log, bukan ke browser
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Transaksi gagal diproses. Silakan coba lagi atau hubungi admin.',
            ], 500);
        }

        [$transaksi, $items] = $result;
        $user = Auth::user();

        return response()->json([
            'success'           => true,
            'message'           => 'Transaksi berhasil diproses!',
            'no_nota'           => $transaksi->no_nota,
            'tanggal_transaksi' => $transaksi->tanggal_transaksi->format('d M Y, H:i'),
            'kasir_nama'        => $user->nama_lengkap ?? $user->username,
            'items'             => $items,
            'total_item'        => (int) $transaksi->total_item,
            'subtotal'          => (float) $transaksi->subtotal,
            'diskon'            => (float) $transaksi->diskon,
            'total_bayar'       => (float) $transaksi->total_bayar,
            'jumlah_bayar'      => (float) $transaksi->jumlah_bayar,
            'kembalian'         => (float) $transaksi->kembalian,
            'metode_pembayaran' => match ($transaksi->metode_pembayaran) {
                'cash'  => 'Tunai',
                'qris'  => 'QRIS',
                default => 'Debit',
            },
        ]);
    }

    /**
     * Nomor nota unik, contoh: TRX-20261123-0942-7F3A.
     */
    private function generateNoNota(): string
    {
        do {
            $nota = 'TRX-' . now()->format('Ymd-Hi') . '-' . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        } while (Transaksi::where('no_nota', $nota)->exists());

        return $nota;
    }
}
