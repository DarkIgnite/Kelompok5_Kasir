@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('page_title', 'Detail Transaksi')

@section('content')
@php
    $metode = ['cash' => 'Tunai', 'debit' => 'Debit', 'qris' => 'QRIS'][$transaksi->metode_pembayaran] ?? $transaksi->metode_pembayaran;
    $rp = fn ($n) => 'Rp' . number_format($n, 0, ',', '.');
@endphp
<div class="space-y-[15px]">
    <div class="no-print flex items-center justify-between gap-3 flex-wrap">
        <a href="{{ route('transaksi.index', ['tanggal' => $transaksi->tanggal_transaksi->toDateString()]) }}" class="text-[12px] font-semibold text-[#525252] hover:text-black inline-flex items-center gap-1">
            &larr; Kembali ke Riwayat Transaksi
        </a>
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="window.print()" class="h-[46px] px-4 bg-white border border-black/30 rounded-[10px] text-[15px] font-semibold text-black hover:bg-neutral-50 cursor-pointer">
                Cetak Struk
            </button>
            @if($bisaBatal)
                <form method="POST" action="{{ route('transaksi.batal', $transaksi) }}" onsubmit="return confirm('Batalkan transaksi {{ $transaksi->no_nota }}? Stok akan dikembalikan.');">
                    @csrf
                    <button type="submit" class="h-[46px] px-4 bg-[#DC2626] text-white border border-black/30 rounded-[10px] text-[15px] font-semibold hover:bg-red-700 cursor-pointer">
                        Batalkan Transaksi
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="no-print">@include('transaksi.partials.alert')</div>

    {{-- STRUK --}}
    <div id="struk" class="w-[392px] max-w-full mx-auto bg-white rounded-2xl border border-black/15 p-[25px] text-black">
        <div class="text-center">
            <p class="text-base font-bold">KasirAja</p>
            <p class="text-[10px] text-black/60">Toko Sepatu · Struk Pembelian</p>
        </div>

        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

        <div class="space-y-1 text-xs">
            <div class="flex justify-between"><span class="text-black/60">No. nota</span><span class="font-normal">{{ $transaksi->no_nota }}</span></div>
            <div class="flex justify-between"><span class="text-black/60">Tanggal</span><span class="font-normal">{{ $transaksi->tanggal_transaksi->format('d M Y, H:i') }}</span></div>
            <div class="flex justify-between"><span class="text-black/60">Kasir</span><span class="font-normal">{{ $transaksi->pengguna->nama_lengkap ?? '-' }}</span></div>
        </div>

        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

        <div class="space-y-3">
            @foreach($transaksi->detail as $d)
                <div>
                    <div class="font-bold text-sm">{{ $d->varian->produk->nama_produk ?? 'Produk dihapus' }}</div>
                    <div class="text-[10px] text-black/60 mt-0.5">
                        Ukuran {{ $d->varian->ukuran ?? '-' }} EUR · {{ $d->varian->warna ?? '-' }} · {{ $d->varian->sku ?? '-' }}
                    </div>
                    <div class="flex justify-between text-xs mt-1">
                        <span>{{ $d->jumlah }} x {{ $rp($d->harga_satuan) }}</span>
                        <span class="font-normal">{{ $rp($d->subtotal) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

        <div class="space-y-1.5 text-xs">
            <div class="flex justify-between"><span class="text-black/60">Subtotal ({{ $transaksi->total_item }} pasang)</span><span class="font-normal">{{ $rp($transaksi->subtotal) }}</span></div>
            <div class="flex justify-between"><span class="text-black/60">Diskon</span><span class="font-normal">{{ $rp($transaksi->diskon) }}</span></div>
        </div>

        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

        <div class="space-y-1.5 text-xs">
            <div class="flex justify-between text-sm font-semibold"><span>Total bayar</span><span>{{ $rp($transaksi->total_bayar) }}</span></div>
            <div class="flex justify-between"><span class="text-black/60">Metode</span><span class="font-normal">{{ $metode }}</span></div>
            <div class="flex justify-between"><span class="text-black/60">Jumlah bayar</span><span class="font-normal">{{ $rp($transaksi->jumlah_bayar) }}</span></div>
            <div class="flex justify-between"><span class="text-black/60">Kembalian</span><span class="font-normal">{{ $rp($transaksi->kembalian) }}</span></div>
        </div>

        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

        <div class="text-center">
            @if($transaksi->status === 'selesai')
                <span class="inline-flex items-center h-[21px] px-[9px] rounded-full bg-[#ECFDF5] border border-[#00A854]/30 text-[11px] font-semibold text-[#00A854]">SELESAI</span>
            @elseif($transaksi->status === 'dibatalkan')
                <span class="inline-flex items-center h-[21px] px-[9px] rounded-full bg-red-50 border border-red-600/30 text-[11px] font-semibold text-red-600">DIBATALKAN</span>
            @else
                <span class="inline-flex items-center h-[21px] px-[9px] rounded-full bg-amber-50 border border-amber-600/30 text-[11px] font-semibold text-amber-600">RETUR</span>
            @endif
            <p class="text-[12px] text-black/60 mt-[15px]">Terimakasih sudah berbelanja.</p>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #struk, #struk * { visibility: visible; }
        #struk { position: absolute; left: 0; top: 0; width: 100%; max-width: 80mm; border: 0; box-shadow: none; padding: 0; }
    }
</style>
@endsection
