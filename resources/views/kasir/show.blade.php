@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('page_title', 'Detail Transaksi')
@section('page_subtitle', 'Informasi detail transaksi kasir.')

@section('content')
<div class="bg-white rounded-2xl border border-neutral-200/90 p-8 shadow-sm">
    <div class="mb-4">
        <a href="{{ route('transaksi.index') }}" class="text-xs font-semibold text-neutral-600 hover:text-neutral-900 inline-flex items-center gap-1">
            &larr; Kembali ke Daftar Transaksi
        </a>
    </div>
    <div class="p-6 bg-neutral-50 rounded-xl border border-neutral-200 text-center">
        <p class="text-sm text-neutral-600">Detail transaksi #{{ $transaksi->nomor_transaksi ?? $transaksi->id_transaksi ?? '' }}</p>
    </div>
</div>
@endsection
