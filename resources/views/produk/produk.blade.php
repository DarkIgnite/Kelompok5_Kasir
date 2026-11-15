@extends('layouts.app')

@section('title', 'Produk & Inventaris')
@section('page_title', 'Produk & Inventaris')
@section('page_subtitle', 'Kelola daftar sepatu, varian warna/ukuran, dan stok barang.')

@section('content')
<div class="bg-white rounded-2xl border border-neutral-200/90 p-8 shadow-sm flex flex-col items-center justify-center text-center min-h-[420px]">
    <div class="w-16 h-16 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-800 mb-4 shadow-xs">
        <!-- 3D Box Icon -->
        <svg class="w-8 h-8 text-neutral-800" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="14,2.5 25,8.5 14,14.5 3,8.5"></polygon>
            <line x1="8.5" y1="5.5" x2="19.5" y2="11.5"></line>
            <polyline points="3,8.5 3,20.5 14,26.5 14,14.5"></polyline>
            <polyline points="25,8.5 25,20.5 14,26.5"></polyline>
        </svg>
    </div>
    <h3 class="text-lg font-bold text-neutral-900 tracking-tight">Halaman Produk & Inventaris</h3>
    <p class="text-sm text-neutral-500 max-w-md mt-1 mb-6">
        Halaman ini sudah terhubung dengan navigasi sidebar. Silakan berikan tampilan desain yang ingin diterapkan untuk halaman ini.
    </p>
    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-neutral-100 border border-neutral-200 text-xs font-semibold text-neutral-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Route: <code>/produk</code> | Total Data: {{ isset($produk) ? $produk->total() : 0 }} produk</span>
    </div>
</div>
@endsection
