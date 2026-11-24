@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('page_title', 'Riwayat Transaksi')

@section('content')
<div class="space-y-[15px]">

    @include('transaksi.partials.alert')

    {{-- TOOLBAR (urutan & ukuran sama dengan Figma: cari, Filter, Show, tanggal, Tampilkan) --}}
    <form method="GET" action="{{ route('transaksi.index') }}" class="flex items-center gap-[15px]">
        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
        @if(request()->boolean('saya'))<input type="hidden" name="saya" value="1">@endif
        @if(request('limit'))<input type="hidden" name="limit" value="{{ request('limit') }}">@endif
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-[15px] flex items-center pointer-events-none text-neutral-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No. Nota"
                class="w-full h-[46px] pl-[54px] pr-4 rounded-[13px] bg-white border border-black/30 text-[15px] text-neutral-800 placeholder-neutral-400 focus:outline-none focus:border-neutral-900">
        </div>

        <!-- Filter: status & nota saya -->
        <div class="relative" id="dropdownFilterWrapper">
            <button type="button" id="btnDropdownFilter" class="h-[46px] px-[10px] bg-white border border-black/30 rounded-[10px] flex items-center gap-[10px] text-[15px] text-black hover:bg-neutral-50 cursor-pointer">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <span>Filter</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="menuDropdownFilter" class="hidden absolute right-0 mt-1 w-56 bg-white border border-neutral-200 rounded-xl shadow-xl z-30 p-3 text-xs space-y-3">
                <div>
                    <span class="font-bold text-neutral-800 uppercase tracking-wider block mb-1.5 text-[10px]">Status</span>
                    <div class="space-y-1">
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ !request('status') ? 'font-bold bg-neutral-100' : '' }}">Semua Status</a>
                        <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('status') === 'selesai' ? 'font-bold bg-neutral-100 text-emerald-600' : '' }}">Selesai</a>
                        <a href="{{ request()->fullUrlWithQuery(['status' => 'dibatalkan']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('status') === 'dibatalkan' ? 'font-bold bg-neutral-100 text-red-600' : '' }}">Dibatalkan</a>
                    </div>
                </div>
                <div class="pt-2 border-t border-neutral-100">
                    <span class="font-bold text-neutral-800 uppercase tracking-wider block mb-1.5 text-[10px]">Kasir</span>
                    <div class="space-y-1">
                        <a href="{{ request()->fullUrlWithQuery(['saya' => null]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ !request()->boolean('saya') ? 'font-bold bg-neutral-100' : '' }}">Semua Kasir</a>
                        <a href="{{ request()->fullUrlWithQuery(['saya' => 1]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request()->boolean('saya') ? 'font-bold bg-neutral-100' : '' }}">Nota saya</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show -->
        <div class="relative" id="dropdownShowWrapper">
            <button type="button" id="btnDropdownShow" class="h-[46px] px-[10px] bg-white border border-black/30 rounded-[10px] flex items-center gap-[10px] text-[15px] text-black hover:bg-neutral-50 cursor-pointer">
                <span>Show <span class="text-neutral-400 underline underline-offset-2">{{ $transaksi->perPage() }}</span></span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div id="menuDropdownShow" class="hidden absolute right-0 mt-1 w-28 bg-white border border-neutral-200 rounded-xl shadow-lg z-20 py-1 text-sm">
                @foreach([10, 25, 50, 100] as $lim)
                    <a href="{{ request()->fullUrlWithQuery(['limit' => $lim]) }}" class="block px-4 py-1.5 text-neutral-700 hover:bg-neutral-100 {{ $transaksi->perPage() == $lim ? 'font-bold bg-neutral-50' : '' }}">Show {{ $lim }}</a>
                @endforeach
            </div>
        </div>

        <!-- Tanggal -->
        <label class="h-[46px] px-[10px] bg-white border border-black/30 rounded-[10px] flex items-center gap-[10px] text-[15px] font-semibold text-black cursor-pointer">
            <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3.5" y="5" width="17" height="15.5" rx="2"></rect><path d="M3.5 9.5h17M8 3v4M16 3v4" stroke-linecap="round"></path></svg>
            <input type="date" name="tanggal" id="inputTanggal" value="{{ $tanggal }}" max="{{ now()->toDateString() }}" required class="bg-transparent focus:outline-none cursor-pointer [&::-webkit-calendar-picker-indicator]:hidden">
        </label>

        <button type="submit" class="h-[46px] px-[10px] bg-black text-white border border-black/30 rounded-[10px] text-[15px] font-semibold hover:bg-neutral-800 cursor-pointer">
            Tampilkan
        </button>
    </form>

    {{-- RINGKASAN HARI INI --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-[15px]">
        <div class="bg-white rounded-2xl border border-[#E7E7E7] px-[22px] py-5">
            <p class="text-[12px] font-medium text-[#737373]">Nota selesai</p>
            <p class="text-2xl font-bold text-black mt-[6px] leading-[29px]">{{ number_format($ringkasan->jumlah_nota, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-[#E7E7E7] px-[22px] py-5">
            <p class="text-[12px] font-medium text-[#737373]">Pasang terjual</p>
            <p class="text-2xl font-bold text-black mt-[6px] leading-[29px]">{{ number_format($ringkasan->total_pasang, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-[#E7E7E7] px-[22px] py-5">
            <p class="text-[12px] font-medium text-[#737373]">Total penjualan</p>
            <p class="text-2xl font-bold text-black mt-[6px] leading-[29px]">Rp{{ number_format($ringkasan->total_penjualan, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl border border-neutral-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto px-5">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/10">
                        <th class="h-[46px] py-0 w-[60px] px-0 text-[12px] font-semibold text-black text-center">No.</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black">No. Nota</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black">Jam</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black">Kasir</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black">Item</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black">Total Bayar</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black">Metode</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black">Status</th>
                        <th class="h-[46px] py-0 pl-0 pr-5 text-[12px] font-semibold text-black text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($transaksi as $item)
                        <tr class="hover:bg-neutral-50/60 transition-colors">
                            <td class="h-[59px] py-0 w-[60px] px-0 text-[12px] font-medium text-neutral-800 text-center">{{ $transaksi->firstItem() + $loop->index }}</td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-[12px] font-semibold text-neutral-900 whitespace-nowrap">{{ $item->no_nota }}</td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-[12px] text-neutral-700">{{ $item->tanggal_transaksi->format('H:i') }}</td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-[12px] text-neutral-700">{{ $item->pengguna->nama_lengkap ?? '-' }}</td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-[12px] text-neutral-700">{{ $item->total_item }} pasang</td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-[12px] font-semibold text-neutral-900 whitespace-nowrap">Rp{{ number_format($item->total_bayar, 0, ',', '.') }}</td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-[12px] text-neutral-700">{{ ['cash' => 'Tunai', 'debit' => 'Debit', 'qris' => 'QRIS'][$item->metode_pembayaran] ?? $item->metode_pembayaran }}</td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-xs font-semibold">
                                @if($item->status === 'selesai')
                                    <span class="text-emerald-500">Selesai</span>
                                @elseif($item->status === 'dibatalkan')
                                    <span class="text-red-500">Dibatalkan</span>
                                @else
                                    <span class="text-amber-500">Retur</span>
                                @endif
                            </td>
                            <td class="h-[59px] py-0 pl-0 pr-5 text-center">
                                <a href="{{ route('transaksi.show', $item) }}" class="inline-flex px-4 py-1.5 rounded-lg border border-neutral-300 text-xs font-semibold text-neutral-800 hover:bg-neutral-50 transition">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-16 text-center text-sm text-neutral-400">Belum ada transaksi pada tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($transaksi->hasPages())
        <div>{{ $transaksi->links() }}</div>
    @endif
</div>
@push('scripts')
<script>
    (function () {
        // Tanggal: klik di mana saja langsung buka kalender, pilih tanggal langsung filter
        const tgl = document.getElementById('inputTanggal');
        tgl?.closest('label')?.addEventListener('click', (e) => {
            e.preventDefault();
            try { tgl.showPicker(); } catch (_) { tgl.focus(); }
        });
        tgl?.addEventListener('change', () => { if (tgl.value) tgl.form.submit(); });

        const pairs = [['btnDropdownFilter', 'menuDropdownFilter'], ['btnDropdownShow', 'menuDropdownShow']];
        pairs.forEach(([b, m]) => {
            const btn = document.getElementById(b), menu = document.getElementById(m);
            btn?.addEventListener('click', (e) => {
                e.stopPropagation();
                pairs.forEach(([, o]) => { if (o !== m) document.getElementById(o)?.classList.add('hidden'); });
                menu.classList.toggle('hidden');
            });
        });
        document.addEventListener('click', () => pairs.forEach(([, m]) => document.getElementById(m)?.classList.add('hidden')));
    })();
</script>
@endpush
@endsection
