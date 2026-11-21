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
    Route::post('/dashboard/simulate-sale', [DashboardController::class, 'simulateSale'])->name('dashboard.simulate');

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
        Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');

        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/produk/{produk}', [ProdukController::class, 'show'])->name('produk.show');

        Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

