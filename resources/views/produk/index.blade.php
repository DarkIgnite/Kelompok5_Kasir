@extends('layouts.app')

@section('title', 'List Produk')
@section('page_title', 'List Produk')

@section('content')
<div class="space-y-5">

    {{-- Alert Flash Message --}}
    @if(session('success'))
        <div id="toast-success" class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm transition-all duration-300">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="document.getElementById('toast-success').remove()" class="text-emerald-600 hover:text-emerald-900 font-bold ml-4">&times;</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- TOOLBAR / FILTER & ACTION ROW --}}
    <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <!-- Search Field -->
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <input 
                type="text" 
                id="searchProduk" 
                placeholder="Cari Nama Produk, Kode Produk" 
                value="{{ request('search') }}"
                class="w-full h-11 pl-10 pr-4 rounded-xl bg-white border border-neutral-300 text-sm text-neutral-800 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors shadow-2xs"
            >
        </div>

        <!-- Right Controls: Filter, Show Entries, Export CSV, Tambah Data -->
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap justify-end">
            <!-- Filter Dropdown Button -->
            <div class="relative" id="dropdownFilterWrapper">
                <button 
                    type="button" 
                    id="btnDropdownFilter" 
                    class="h-11 px-4 bg-white border border-neutral-300 rounded-xl flex items-center gap-2 text-sm text-neutral-700 hover:bg-neutral-50 transition shadow-2xs cursor-pointer"
                >
                    <!-- Funnel / Filter Icon -->
                    <svg class="w-4 h-4 text-neutral-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    <span>Filter</span>
                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="menuDropdownFilter" class="hidden absolute right-0 mt-1 w-56 bg-white border border-neutral-200 rounded-xl shadow-xl z-30 p-3 text-xs space-y-3">
                    <div>
                        <span class="font-bold text-neutral-800 uppercase tracking-wider block mb-1.5 text-[10px]">Status</span>
                        <div class="space-y-1">
                            <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ !request('status') ? 'font-bold bg-neutral-100' : '' }}">Semua Status</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'aktif']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('status') === 'aktif' ? 'font-bold bg-neutral-100 text-emerald-600' : '' }}">Aktif</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'nonaktif']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('status') === 'nonaktif' ? 'font-bold bg-neutral-100 text-red-600' : '' }}">Nonaktif</a>
                        </div>
                    </div>
                    @if(isset($kategoriList) && $kategoriList->count() > 0)
                    <div class="pt-2 border-t border-neutral-100">
                        <span class="font-bold text-neutral-800 uppercase tracking-wider block mb-1.5 text-[10px]">Kategori</span>
                        <div class="space-y-1 max-h-36 overflow-y-auto">
                            <a href="{{ request()->fullUrlWithQuery(['kategori' => null]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ !request('kategori') ? 'font-bold bg-neutral-100' : '' }}">Semua Kategori</a>
                            @foreach($kategoriList as $kat)
                                <a href="{{ request()->fullUrlWithQuery(['kategori' => $kat->id_kategori]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('kategori') == $kat->id_kategori ? 'font-bold bg-neutral-100' : '' }}">
                                    {{ $kat->nama_kategori }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Show 100 Dropdown -->
            <div class="relative" id="dropdownShowWrapper">
                <button 
                    type="button" 
                    id="btnDropdownShow" 
                    class="h-11 px-4 bg-white border border-neutral-300 rounded-xl flex items-center gap-2 text-sm text-neutral-700 hover:bg-neutral-50 transition shadow-2xs cursor-pointer"
                >
                    <span>Show <span class="underline decoration-neutral-400 underline-offset-2">{{ request('limit', 100) }}</span></span>
                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="menuDropdownShow" class="hidden absolute right-0 mt-1 w-28 bg-white border border-neutral-200 rounded-xl shadow-lg z-20 py-1 text-sm">
                    @foreach([10, 25, 50, 100] as $lim)
                        <a href="{{ request()->fullUrlWithQuery(['limit' => $lim]) }}" class="block px-4 py-1.5 text-neutral-700 hover:bg-neutral-100 {{ request('limit', 100) == $lim ? 'font-bold bg-neutral-50' : '' }}">
                            Show {{ $lim }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Export CSV -->
            <button 
                type="button" 
                onclick="exportProdukCSV()" 
                class="h-11 px-4 bg-white border border-neutral-300 rounded-xl flex items-center gap-2 text-sm font-semibold text-neutral-800 hover:bg-neutral-50 transition shadow-2xs cursor-pointer"
            >
                <svg class="w-4 h-4 text-neutral-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <polyline points="12 9 16 13 12 17"></polyline>
                </svg>
                <span>Export CSV</span>
            </button>

            <!-- + Tambah Data -->
            <a 
                href="{{ route('produk.create') }}" 
                class="h-11 px-5 bg-black text-white rounded-xl flex items-center gap-2 text-sm font-bold hover:bg-neutral-800 transition shadow-sm cursor-pointer whitespace-nowrap"
            >
                <span class="text-base font-bold leading-none">+</span>
                <span>Tambah Data</span>
            </a>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-2xl border border-neutral-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="produkTable">
                <thead>
                    <tr class="border-b border-neutral-100">
                        <th class="py-4 pl-6 pr-2 text-xs font-bold text-neutral-800 w-16 text-center">No.</th>
                        <th class="py-4 pl-2 pr-6 text-xs font-bold text-neutral-800">Nama Produk</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Merek</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Kategori</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Harga Jual</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Status</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Akumulasi Stok</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Created at</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800 w-48 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100" id="produkTbody">
                    @forelse($produk as $item)
                        @php
                            $totalStok = $item->varian->sum('stok');
                        @endphp
                        <tr class="hover:bg-neutral-50/60 transition-colors row-produk" 
                            data-nama="{{ strtolower($item->nama_produk) }}" 
                            data-kode="{{ strtolower($item->kode_produk) }}"
                            data-merek="{{ strtolower($item->merek) }}"
                            data-kategori="{{ strtolower($item->kategori->nama_kategori ?? '') }}"
                            data-status="{{ strtolower($item->status) }}"
                            data-id="{{ $item->id_produk }}"
                        >
                            {{-- 1. No. --}}
                            <td class="py-4 pl-6 pr-2 text-sm font-medium text-neutral-800 align-middle text-center">
                                {{ $loop->iteration }}
                            </td>

                            {{-- 2. Nama Produk (with thumbnail) --}}
                            <td class="py-4 pl-2 pr-6 align-middle">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-lg bg-neutral-100/90 border border-neutral-200/80 p-1 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($item->gambar && file_exists(public_path($item->gambar)))
                                            <img src="{{ asset($item->gambar) }}" alt="{{ $item->nama_produk }}" class="w-full h-full object-contain">
                                        @else
                                            <svg class="w-5 h-5 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <polyline points="21 15 16 10 5 21"></polyline>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-neutral-900 leading-tight">
                                            {{ $item->nama_produk }}
                                        </div>
                                        <div class="text-[11px] text-neutral-500 font-medium mt-0.5">
                                            {{ $item->kode_produk }}
                                        </div>
                                        <div class="text-[11px] text-neutral-400 font-medium">
                                            ID : {{ $item->id_produk }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- 3. Merek --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->merek }}
                            </td>

                            {{-- 4. Kategori --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->kategori->nama_kategori ?? '-' }}
                            </td>

                            {{-- 5. Harga Jual --}}
                            <td class="py-4 px-6 text-sm font-semibold text-neutral-900 align-middle">
                                {{ number_format($item->harga_jual, 0, ',', '.') }}
                            </td>

                            {{-- 6. Status --}}
                            <td class="py-4 px-6 text-xs font-semibold align-middle">
                                @if(strtolower($item->status) === 'aktif')
                                    <span class="text-emerald-500">Aktif</span>
                                @else
                                    <span class="text-red-500">Nonaktif</span>
                                @endif
                            </td>

                            {{-- 7. Akumulasi Stok --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-800 align-middle">
                                {{ number_format($totalStok, 0, ',', '.') }}
                            </td>

                            {{-- 8. Created at --}}
                            <td class="py-4 px-6 text-sm font-normal text-neutral-700 align-middle whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMMM Y') }}
                            </td>

                            {{-- 9. Aksi --}}
                            <td class="py-4 px-6 align-middle">
                                <div class="flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <a 
                                        href="{{ route('produk.edit', $item->id_produk) }}" 
                                        class="px-3 py-1.5 rounded-lg border border-neutral-200 bg-white text-xs font-semibold text-neutral-700 hover:bg-neutral-50 hover:border-neutral-300 transition-colors flex items-center gap-1.5 shadow-2xs"
                                    >
                                        <svg class="w-3.5 h-3.5 text-neutral-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    <!-- Hapus Button -->
                                    <button 
                                        type="button" 
                                        onclick="openHapusProdukModal({{ $item->id_produk }}, '{{ addslashes($item->nama_produk) }}')" 
                                        class="px-3 py-1.5 rounded-lg border border-red-200 bg-white text-xs font-semibold text-red-500 hover:bg-red-50 hover:border-red-300 transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-sm text-neutral-400">
                                Belum ada data produk. Klik tombol <strong>+ Tambah Data</strong> di atas untuk membuat produk baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABLE FOOTER / PAGINATION --}}
        <div class="px-6 py-4 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs font-medium text-neutral-600">
                Menampilkan <span id="labelShowingProduk">1–{{ $produk->count() }}</span> dari <span id="labelTotalProduk">{{ method_exists($produk, 'total') ? $produk->total() : $produk->count() }}</span> produk
            </div>

            <div class="flex items-center gap-1">
                @if($produk instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    @if ($produk->onFirstPage())
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-300 cursor-not-allowed text-xs">&lsaquo;</span>
                    @else
                        <a href="{{ $produk->previousPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&lsaquo;</a>
                    @endif

                    @foreach ($produk->getUrlRange(1, $produk->lastPage()) as $page => $url)
                        @if ($page == $produk->currentPage())
                            <span class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center text-xs font-bold">{{ $page }}</span>
                        @elseif($page <= 4 || $page == $produk->lastPage() || abs($page - $produk->currentPage()) <= 1)
                            <a href="{{ $url }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs font-medium">{{ $page }}</a>
                        @elseif($page == 5)
                            <span class="w-7 h-7 flex items-center justify-center text-neutral-400 text-xs">...</span>
                        @endif
                    @endforeach

                    @if ($produk->hasMorePages())
                        <a href="{{ $produk->nextPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&rsaquo;</a>
                    @else
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-300 cursor-not-allowed text-xs">&rsaquo;</span>
                    @endif
                @else
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-300 text-xs">&lsaquo;</span>
                    <span class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center text-xs font-bold">1</span>
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-300 text-xs">&rsaquo;</span>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- MODAL KONFIRMASI HAPUS PRODUK --}}
<div id="modalHapusProduk" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative animate-in fade-in zoom-in-95 duration-150 text-center">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 class="text-base font-bold text-neutral-900 mb-1">Hapus Produk?</h3>
        <p class="text-xs text-neutral-500 mb-6">
            Apakah Anda yakin ingin menghapus produk <span id="namaHapusProdukPlaceholder" class="font-bold text-neutral-800"></span>? Semua varian produk ini juga akan ikut terhapus.
        </p>

        <form action="" method="POST" id="formHapusProduk" class="flex items-center justify-center gap-2.5">
            @csrf
            @method('DELETE')
            <button 
                type="button" 
                onclick="closeHapusProdukModal()" 
                class="w-full py-2.5 rounded-xl border border-neutral-300 bg-white text-sm font-semibold text-neutral-700 hover:bg-neutral-50 transition cursor-pointer"
            >
                Batal
            </button>
            <button 
                type="submit" 
                class="w-full py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition shadow-sm cursor-pointer"
            >
                Hapus
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1. Dropdown Filter Menu
    const btnDropdownFilter = document.getElementById('btnDropdownFilter');
    const menuDropdownFilter = document.getElementById('menuDropdownFilter');
    if (btnDropdownFilter && menuDropdownFilter) {
        btnDropdownFilter.addEventListener('click', (e) => {
            e.stopPropagation();
            menuDropdownFilter.classList.toggle('hidden');
            if (menuDropdownShow) menuDropdownShow.classList.add('hidden');
        });
    }

    // 2. Dropdown Show Menu
    const btnDropdownShow = document.getElementById('btnDropdownShow');
    const menuDropdownShow = document.getElementById('menuDropdownShow');
    if (btnDropdownShow && menuDropdownShow) {
        btnDropdownShow.addEventListener('click', (e) => {
            e.stopPropagation();
            menuDropdownShow.classList.toggle('hidden');
            if (menuDropdownFilter) menuDropdownFilter.classList.add('hidden');
        });
    }

    document.addEventListener('click', () => {
        if (menuDropdownFilter) menuDropdownFilter.classList.add('hidden');
        if (menuDropdownShow) menuDropdownShow.classList.add('hidden');
    });

    // 3. Modal Hapus Handlers
    function openHapusProdukModal(id, nama) {
        const modal = document.getElementById('modalHapusProduk');
        const form = document.getElementById('formHapusProduk');
        form.action = "{{ url('/produk') }}/" + id;
        document.getElementById('namaHapusProdukPlaceholder').textContent = `"${nama}"`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeHapusProdukModal() {
        const modal = document.getElementById('modalHapusProduk');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    window.addEventListener('click', (e) => {
        const modal = document.getElementById('modalHapusProduk');
        if (e.target === modal) {
            closeHapusProdukModal();
        }
    });

    // 4. Client-Side Live Filter Search
    const searchInput = document.getElementById('searchProduk');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.row-produk');
            let visibleCount = 0;

            rows.forEach(row => {
                const nama = row.getAttribute('data-nama') || '';
                const kode = row.getAttribute('data-kode') || '';
                const merek = row.getAttribute('data-merek') || '';
                const kat = row.getAttribute('data-kategori') || '';
                const id = row.getAttribute('data-id') || '';

                if (nama.includes(query) || kode.includes(query) || merek.includes(query) || kat.includes(query) || id.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const labelShowing = document.getElementById('labelShowingProduk');
            if (labelShowing) {
                labelShowing.textContent = visibleCount > 0 ? `1–${visibleCount}` : '0';
            }
        });
    }

    // 5. Export to CSV Function
    function exportProdukCSV() {
        const rows = document.querySelectorAll('#produkTable tbody tr.row-produk');
        let csvContent = "data:text/csv;charset=utf-8,No,ID Produk,Kode Produk,Nama Produk,Merek,Kategori,Harga Jual,Status,Akumulasi Stok,Created At\n";

        let index = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const id = row.getAttribute('data-id');
                const kode = row.querySelector('td:nth-child(2) .text-\\[11px\\]').textContent.trim();
                const nama = row.querySelector('td:nth-child(2) .font-semibold').textContent.trim();
                const merek = row.querySelector('td:nth-child(3)').textContent.trim();
                const kategori = row.querySelector('td:nth-child(4)').textContent.trim();
                const harga = row.querySelector('td:nth-child(5)').textContent.trim();
                const status = row.querySelector('td:nth-child(6)').textContent.trim();
                const stok = row.querySelector('td:nth-child(7)').textContent.trim();
                const createdAt = row.querySelector('td:nth-child(8)').textContent.trim();

                csvContent += `"${index}","${id}","${kode}","${nama.replace(/"/g, '""')}","${merek.replace(/"/g, '""')}","${kategori.replace(/"/g, '""')}","${harga}","${status}","${stok}","${createdAt}"\n`;
                index++;
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `produk_kasiraja_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
