<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\TransaksiController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    /*
     * Hak akses mengikuti DFD Level 2:
     * - Kasir : transaksi penjualan (5.2), riwayat (5.1), batalkan (5.3), detail/cetak struk (6.1).
     *           Melihat kategori/produk/stok (1.1, 2.1, 3.1) lewat katalog di halaman kasir.
     * - Admin & Kasir : dashboard.
     * - Admin : kelola kategori, produk, varian & stok, pengguna (1.2-1.5, 2.2-2.5, 3.3-3.6, 4.3-4.6).
     */
    // Dashboard: bisa dilihat admin & kasir (keputusan tim, belum ada di DFD -> tambahkan proses 7.0)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getRealtimeData'])->name('dashboard.data');

    Route::middleware('role:kasir')->group(function () {
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::post('/kasir/transaksi', [KasirController::class, 'store'])->name('kasir.store');

        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'show'])->name('transaksi.show');
        Route::post('/transaksi/{transaksi}/batal', [TransaksiController::class, 'batal'])->name('transaksi.batal');
    });

    // Halaman khusus Admin (Kategori, Produk, Varian, Pengguna)
    Route::middleware('role:admin')->group(function () {

        // Kategori Routes
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        // Produk Routes
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/produk/create', [ProdukController::class, 'create'])->name('produk.create');
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::get('/produk/{produk}/edit', [ProdukController::class, 'edit'])->name('produk.edit');
        Route::put('/produk/{produk}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{produk}', [ProdukController::class, 'destroy'])->name('produk.destroy');
        Route::get('/produk/{produk}', [ProdukController::class, 'show'])->name('produk.show');

        // Varian Produk Routes
        Route::get('/varian-produk', [ProdukController::class, 'varianIndex'])->name('varian.index');
        Route::post('/varian-produk', [ProdukController::class, 'varianStore'])->name('varian.store');
        Route::put('/varian-produk/{varian}', [ProdukController::class, 'varianUpdate'])->name('varian.update');
        Route::delete('/varian-produk/{varian}', [ProdukController::class, 'varianDestroy'])->name('varian.destroy');

        // Pengguna / User Sistem Routes
        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
        Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])->name('pengguna.update');
        Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy'])->name('pengguna.destroy');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

