@extends('layouts.app')

@section('title', 'Pengguna & Pelanggan')
@section('page_title', 'Pengguna & Pelanggan')
@section('page_subtitle', 'Kelola akun kasir, admin, dan data member/pelanggan.')

@section('content')
<div class="bg-white rounded-2xl border border-neutral-200/90 p-8 shadow-sm flex flex-col items-center justify-center text-center min-h-[420px]">
    <div class="w-16 h-16 rounded-2xl bg-neutral-100 flex items-center justify-center text-neutral-800 mb-4 shadow-xs">
        <!-- Users Icon -->
        <svg class="w-8 h-8 text-neutral-800" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 6a4.5 4.5 0 0 1 1.2 8.5" stroke="#737373"></path>
            <path d="M19.5 21a7 7 0 0 1 3.2 4.5" stroke="#737373"></path>
            <circle cx="11" cy="9.5" r="4.5"></circle>
            <path d="M4 25.5a7 7 0 0 1 14 0"></path>
        </svg>
    </div>
    <h3 class="text-lg font-bold text-neutral-900 tracking-tight">Halaman Pengguna & Pelanggan</h3>
    <p class="text-sm text-neutral-500 max-w-md mt-1 mb-6">
        Halaman ini sudah terhubung dengan navigasi sidebar. Silakan berikan tampilan desain yang ingin diterapkan untuk halaman ini.
    </p>
    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-neutral-100 border border-neutral-200 text-xs font-semibold text-neutral-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Route: <code>/pengguna</code> | Total Data: {{ isset($pengguna) ? $pengguna->total() : 0 }} pengguna</span>
    </div>
</div>
@endsection
