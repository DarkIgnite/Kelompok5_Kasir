<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PenggunaController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getRealtimeData'])->name('dashboard.data');

    // Sidebar Page Routes (Bisa diakses oleh Kasir & Admin)
    Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
    Route::post('/kasir/transaksi', [KasirController::class, 'store'])->name('kasir.store');
    Route::get('/kasir/{kasir}', [KasirController::class, 'show'])->name('kasir.show');

    // Route alias /transaksi untuk kompatibilitas tautan / riwayat
    Route::get('/transaksi', [KasirController::class, 'index'])->name('transaksi.index');
    Route::post('/transaksi', [KasirController::class, 'store'])->name('transaksi.store');
    Route::get('/transaksi/{kasir}', [KasirController::class, 'show'])->name('transaksi.show');

    // Halaman khusus Administrator (Kategori, Produk, Pengguna)
    Route::middleware('can:admin')->group(function () {
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

