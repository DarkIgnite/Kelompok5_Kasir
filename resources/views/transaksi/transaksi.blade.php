@extends('layouts.app')

@section('title', 'Kasir & Transaksi')
@section('page_title', 'Kasir & Transaksi')
@section('page_subtitle', 'Kelola transaksi kasir, penjualan, dan riwayat pesanan.')

@section('content')
<div class="bg-white rounded-2xl border border-neutral-200/90 p-8 shadow-sm flex flex-col items-center justify-center text-center min-h-[420px]">
    <div class="w-16 h-16 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-800 mb-4 shadow-xs">
        <!-- Cash Register Icon -->
        <svg class="w-8 h-8 text-neutral-800" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
            <rect x="8" y="2.5" width="12" height="4" rx="1.5"></rect>
            <path d="M5.5 8.5h17a2.5 2.5 0 0 1 2.45 3l-1.5 10a2.5 2.5 0 0 1-2.45 2H7a2.5 2.5 0 0 1-2.45-2l-1.5-10a2.5 2.5 0 0 1 2.45-3z"></path>
            <circle cx="8" cy="13" r="0.9" fill="currentColor"></circle>
            <circle cx="12" cy="13" r="0.9" fill="currentColor"></circle>
            <circle cx="16" cy="13" r="0.9" fill="currentColor"></circle>
            <circle cx="20" cy="13" r="0.9" fill="currentColor"></circle>
            <circle cx="10" cy="16.5" r="0.9" fill="currentColor"></circle>
            <circle cx="14" cy="16.5" r="0.9" fill="currentColor"></circle>
            <circle cx="18" cy="16.5" r="0.9" fill="currentColor"></circle>
            <line x1="7.5" y1="20" x2="20.5" y2="20"></line>
        </svg>
    </div>
    <h3 class="text-lg font-bold text-neutral-900 tracking-tight">Halaman Kasir & Transaksi</h3>
    <p class="text-sm text-neutral-500 max-w-md mt-1 mb-6">
        Halaman ini sudah terhubung dengan navigasi sidebar. Silakan berikan tampilan desain yang ingin diterapkan untuk halaman ini.
    </p>
    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-neutral-100 border border-neutral-200 text-xs font-semibold text-neutral-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Route: <code>/transaksi</code> | Total Data: {{ isset($transaksi) ? $transaksi->total() : 0 }} transaksi</span>
    </div>
</div>
@endsection
