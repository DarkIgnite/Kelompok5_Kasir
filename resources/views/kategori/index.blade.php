@extends('layouts.app')

@section('title', 'Kategori Produk')
@section('page_title', 'Kategori Produk')
@section('page_subtitle', 'Kelola kategori produk dan pengelompokan barang.')

@section('content')
<div class="bg-white rounded-2xl border border-neutral-200/90 p-8 shadow-sm flex flex-col items-center justify-center text-center min-h-[420px]">
    <div class="w-16 h-16 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-800 mb-4 shadow-xs">
        <!-- Geometric Shapes Icon -->
        <svg class="w-8 h-8 text-neutral-800" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="14,2.5 21.5,13.5 6.5,13.5"></polygon>
            <rect x="3.5" y="16.5" width="8.5" height="8.5" rx="2"></rect>
            <circle cx="20.5" cy="20.75" r="4.75"></circle>
        </svg>
    </div>
    <h3 class="text-lg font-bold text-neutral-900 tracking-tight">Halaman Kategori Produk</h3>
    <p class="text-sm text-neutral-500 max-w-md mt-1 mb-6">
        Halaman ini sudah terhubung dengan navigasi sidebar. Silakan berikan tampilan desain yang ingin diterapkan untuk halaman ini.
    </p>
    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-neutral-100 border border-neutral-200 text-xs font-semibold text-neutral-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Route: <code>/kategori</code> | Total Data: {{ isset($kategori) ? count($kategori) : 0 }} kategori</span>
    </div>
</div>
@endsection
