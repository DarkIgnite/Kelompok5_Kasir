<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\VarianProduk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Riwayat, detail/struk, dan pembatalan transaksi (DFD 5.1, 5.3, 6.1) — khusus kasir.
 */
class TransaksiController extends Controller
{
    /**
     * DFD 5.1 — Melihat riwayat transaksi.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'status'  => ['nullable', 'in:selesai,dibatalkan,retur'],
            'search'  => ['nullable', 'string', 'max:30'],
            'saya'    => ['nullable', 'boolean'],
        ]);

        $tanggal = $validated['tanggal'] ?? now()->toDateString();
        $limit = min(max((int) $request->input('limit', 25), 10), 100);

        $transaksi = Transaksi::with('pengguna:id_pengguna,nama_lengkap,username')
            ->whereDate('tanggal_transaksi', $tanggal)
            ->when($validated['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($validated['search'] ?? null, fn ($q, $s) => $q->where('no_nota', 'like', "%{$s}%"))
            ->when($request->boolean('saya'), fn ($q) => $q->where('id_pengguna', Auth::id()))
            ->orderByDesc('tanggal_transaksi')
            ->paginate($limit)
            ->withQueryString();

        $ringkasan = Transaksi::whereDate('tanggal_transaksi', $tanggal)
            ->where('status', 'selesai')
            ->selectRaw('COUNT(*) as jumlah_nota, COALESCE(SUM(total_item),0) as total_pasang, COALESCE(SUM(total_bayar),0) as total_penjualan')
            ->first();

        return view('transaksi.index', compact('transaksi', 'tanggal', 'ringkasan'));
    }

    /**
     * DFD 6.1 — Melihat detail transaksi / cetak struk.
     */
    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['pengguna:id_pengguna,nama_lengkap,username', 'detail.varian.produk']);

        return view('transaksi.show', [
            'transaksi' => $transaksi,
            'bisaBatal' => $this->bisaDibatalkan($transaksi),
        ]);
    }

    /**
     * DFD 5.3 — Membatalkan transaksi. Stok semua item dikembalikan.
     */
    public function batal(Transaksi $transaksi)
    {
        if (! $this->bisaDibatalkan($transaksi)) {
            return redirect()->route('transaksi.show', $transaksi)->with('error',
                'Transaksi ini tidak bisa dibatalkan. Hanya nota milik Anda sendiri, berstatus selesai, dan dibuat hari ini yang bisa dibatalkan.');
        }

        DB::transaction(function () use ($transaksi) {
            // Kunci baris nota supaya tidak dibatalkan dua kali bersamaan
            $nota = Transaksi::whereKey($transaksi->id_transaksi)->lockForUpdate()->first();
            if ($nota->status !== 'selesai') {
                return;
            }

            foreach ($nota->detail as $item) {
                VarianProduk::whereKey($item->id_varian)->increment('stok', $item->jumlah);
            }

            $nota->update(['status' => 'dibatalkan']);
        });

        return redirect()->route('transaksi.show', $transaksi)
            ->with('success', "Transaksi {$transaksi->no_nota} dibatalkan. Stok sudah dikembalikan.");
    }

    /**
     * Aturan batal: nota milik kasir yang login, status masih selesai, dan dibuat hari ini.
     */
    private function bisaDibatalkan(Transaksi $transaksi): bool
    {
        return $transaksi->status === 'selesai'
            && (int) $transaksi->id_pengguna === (int) Auth::id()
            && $transaksi->tanggal_transaksi->isToday();
    }
}
