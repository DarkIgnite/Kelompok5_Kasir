@extends('layouts.app')

@section('title', 'List Varian Produk')
@section('page_title', 'List Varian Produk')

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

    @if(session('error'))
        <div id="toast-error" class="flex items-center justify-between p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm transition-all duration-300">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="document.getElementById('toast-error').remove()" class="text-red-600 hover:text-red-900 font-bold ml-4">&times;</button>
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
                id="searchVarian" 
                placeholder="Cari SKU, Nama Produk" 
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
                    <svg class="w-4 h-4 text-neutral-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    <span>Filter</span>
                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <div id="menuDropdownFilter" class="hidden absolute right-0 mt-1 w-56 bg-white border border-neutral-200 rounded-xl shadow-xl z-30 p-3 text-xs space-y-2">
                    <span class="font-bold text-neutral-800 uppercase tracking-wider block mb-1 text-[10px]">Filter Produk</span>
                    <div class="space-y-1 max-h-48 overflow-y-auto">
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100">Semua Produk</a>
                        @foreach($produkList as $pr)
                            <a href="{{ request()->fullUrlWithQuery(['search' => $pr->nama_produk]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100">
                                {{ $pr->nama_produk }}
                            </a>
                        @endforeach
                    </div>
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
                onclick="exportVarianCSV()" 
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
            <button 
                type="button" 
                onclick="openTambahVarianModal()" 
                class="h-11 px-5 bg-black text-white rounded-xl flex items-center gap-2 text-sm font-bold hover:bg-neutral-800 transition shadow-sm cursor-pointer whitespace-nowrap"
            >
                <span class="text-base font-bold leading-none">+</span>
                <span>Tambah Data</span>
            </button>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-2xl border border-neutral-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="varianTable">
                <thead>
                    <tr class="border-b border-neutral-100">
                        <th class="py-4 pl-6 pr-2 text-xs font-bold text-neutral-800 w-16 text-center">No.</th>
                        <th class="py-4 pl-2 pr-6 text-xs font-bold text-neutral-800">SKU</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Nama Produk</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Ukuran</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Warna</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Stok</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Stok Minimum</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Created at</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800 w-48 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100" id="varianTbody">
                    @forelse($varian as $item)
                        <tr class="hover:bg-neutral-50/60 transition-colors row-varian" 
                            data-sku="{{ strtolower($item->sku) }}" 
                            data-produk="{{ strtolower($item->produk->nama_produk ?? '') }}"
                            data-ukuran="{{ strtolower($item->ukuran) }}"
                            data-warna="{{ strtolower($item->warna) }}"
                            data-id="{{ $item->id_varian }}"
                        >
                            {{-- 1. No. --}}
                            <td class="py-4 pl-6 pr-2 text-sm font-medium text-neutral-800 align-middle text-center">
                                {{ $loop->iteration }}
                            </td>

                            {{-- 2. SKU --}}
                            <td class="py-4 pl-2 pr-6 align-middle">
                                <div class="text-sm font-semibold text-neutral-900 leading-tight">
                                    {{ $item->sku }}
                                </div>
                                <div class="text-[11px] text-neutral-400 font-medium mt-0.5">
                                    ID : {{ $item->id_varian }}
                                </div>
                            </td>

                            {{-- 3. Nama Produk --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-800 align-middle">
                                {{ $item->produk->nama_produk ?? '-' }}
                            </td>

                            {{-- 4. Ukuran --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->ukuran }}
                            </td>

                            {{-- 5. Warna --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->warna }}
                            </td>

                            {{-- 6. Stok --}}
                            <td class="py-4 px-6 text-sm font-bold align-middle {{ $item->stok <= $item->stok_minimum ? 'text-red-500' : 'text-neutral-900' }}">
                                {{ $item->stok }}
                            </td>

                            {{-- 7. Stok Minimum --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->stok_minimum }}
                            </td>

                            {{-- 8. Created at --}}
                            <td class="py-4 px-6 text-sm font-normal text-neutral-700 align-middle whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMMM Y') }}
                            </td>

                            {{-- 9. Aksi --}}
                            <td class="py-4 px-6 align-middle">
                                <div class="flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        onclick="openEditVarianModal({{ $item->id_varian }}, {{ $item->id_produk }}, '{{ addslashes($item->ukuran) }}', '{{ addslashes($item->warna) }}', {{ $item->stok_minimum }}, {{ $item->stok }})" 
                                        class="px-3 py-1.5 rounded-lg border border-neutral-200 bg-white text-xs font-semibold text-neutral-700 hover:bg-neutral-50 hover:border-neutral-300 transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 text-neutral-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Hapus Button -->
                                    <button 
                                        type="button" 
                                        onclick="openHapusVarianModal({{ $item->id_varian }}, '{{ addslashes($item->sku) }}')" 
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
                                Belum ada data varian produk. Klik tombol <strong>+ Tambah Data</strong> di atas untuk membuat varian baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABLE FOOTER / PAGINATION --}}
        <div class="px-6 py-4 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs font-medium text-neutral-600">
                Menampilkan <span id="labelShowingVarian">1–{{ $varian->count() }}</span> dari <span id="labelTotalVarian">{{ method_exists($varian, 'total') ? $varian->total() : $varian->count() }}</span> varian
            </div>

            <div class="flex items-center gap-1">
                @if($varian instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    @if ($varian->onFirstPage())
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-300 cursor-not-allowed text-xs">&lsaquo;</span>
                    @else
                        <a href="{{ $varian->previousPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&lsaquo;</a>
                    @endif

                    @foreach ($varian->getUrlRange(1, $varian->lastPage()) as $page => $url)
                        @if ($page == $varian->currentPage())
                            <span class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center text-xs font-bold">{{ $page }}</span>
                        @elseif($page <= 4 || $page == $varian->lastPage() || abs($page - $varian->currentPage()) <= 1)
                            <a href="{{ $url }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs font-medium">{{ $page }}</a>
                        @elseif($page == 5)
                            <span class="w-7 h-7 flex items-center justify-center text-neutral-400 text-xs">...</span>
                        @endif
                    @endforeach

                    @if ($varian->hasMorePages())
                        <a href="{{ $varian->nextPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&rsaquo;</a>
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

{{-- MODAL TAMBAH VARIAN  --}}
<div id="modalTambahVarian" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-neutral-100">
            <h3 class="text-base font-bold text-neutral-900 tracking-tight">Tambah Varian Produk</h3>
            <button type="button" onclick="closeTambahVarianModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('varian.store') }}" method="POST" id="formTambahVarian" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="id_produk_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Produk <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select 
                        name="id_produk" 
                        id="id_produk_tambah" 
                        required 
                        class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                    >
                        <option value="" disabled selected>Pilih Produk</option>
                        @foreach($produkList as $pr)
                            <option value="{{ $pr->id_produk }}">{{ $pr->nama_produk }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="ukuran_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Ukuran <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="ukuran" 
                        id="ukuran_tambah" 
                        placeholder="0" 
                        required 
                        class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                    >
                </div>
                <div>
                    <label for="warna_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Warna <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="warna" 
                        id="warna_tambah" 
                        placeholder="Masukkan warna" 
                        required 
                        class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                    >
                </div>
            </div>

            <div>
                <label for="stok_minimum_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Stok minimum <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="stok_minimum" 
                    id="stok_minimum_tambah" 
                    placeholder="0" 
                    value="0"
                    min="0"
                    required 
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                >
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeTambahVarianModal()" 
                    class="px-5 py-2.5 rounded-xl border border-neutral-300 bg-white text-sm font-semibold text-neutral-700 hover:bg-neutral-50 transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-black text-white text-sm font-semibold hover:bg-neutral-800 transition shadow-sm cursor-pointer"
                >
                    Tambah Data
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT VARIAN --}}
<div id="modalEditVarian" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-neutral-100">
            <h3 class="text-base font-bold text-neutral-900 tracking-tight">Edit Varian Produk</h3>
            <button type="button" onclick="closeEditVarianModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="" method="POST" id="formEditVarian" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="id_produk_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Produk <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select 
                        name="id_produk" 
                        id="id_produk_edit" 
                        required 
                        class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                    >
                        @foreach($produkList as $pr)
                            <option value="{{ $pr->id_produk }}">{{ $pr->nama_produk }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="ukuran_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Ukuran <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="ukuran" 
                        id="ukuran_edit" 
                        required 
                        class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 transition-colors"
                    >
                </div>
                <div>
                    <label for="warna_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Warna <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="warna" 
                        id="warna_edit" 
                        required 
                        class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 transition-colors"
                    >
                </div>
            </div>

            <div>
                <label for="stok_minimum_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Stok minimum <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    name="stok_minimum" 
                    id="stok_minimum_edit" 
                    min="0"
                    required 
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 transition-colors"
                >
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeEditVarianModal()" 
                    class="px-5 py-2.5 rounded-xl border border-neutral-300 bg-white text-sm font-semibold text-neutral-700 hover:bg-neutral-50 transition cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-black text-white text-sm font-semibold hover:bg-neutral-800 transition shadow-sm cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS VARIAN --}}
<div id="modalHapusVarian" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative animate-in fade-in zoom-in-95 duration-150 text-center">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 class="text-base font-bold text-neutral-900 mb-1">Hapus Varian?</h3>
        <p class="text-xs text-neutral-500 mb-6">
            Apakah Anda yakin ingin menghapus varian <span id="skuHapusPlaceholder" class="font-bold text-neutral-800"></span>?
        </p>

        <form action="" method="POST" id="formHapusVarian" class="flex items-center justify-center gap-2.5">
            @csrf
            @method('DELETE')
            <button 
                type="button" 
                onclick="closeHapusVarianModal()" 
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
    // Dropdowns
    const btnDropdownFilter = document.getElementById('btnDropdownFilter');
    const menuDropdownFilter = document.getElementById('menuDropdownFilter');
    if (btnDropdownFilter && menuDropdownFilter) {
        btnDropdownFilter.addEventListener('click', (e) => {
            e.stopPropagation();
            menuDropdownFilter.classList.toggle('hidden');
            if (menuDropdownShow) menuDropdownShow.classList.add('hidden');
        });
    }

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

    // Modals
    function openTambahVarianModal() {
        const modal = document.getElementById('modalTambahVarian');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTambahVarianModal() {
        const modal = document.getElementById('modalTambahVarian');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditVarianModal(id, idProduk, ukuran, warna, stokMin) {
        const modal = document.getElementById('modalEditVarian');
        const form = document.getElementById('formEditVarian');
        form.action = "{{ url('/varian-produk') }}/" + id;

        document.getElementById('id_produk_edit').value = idProduk;
        document.getElementById('ukuran_edit').value = ukuran;
        document.getElementById('warna_edit').value = warna;
        document.getElementById('stok_minimum_edit').value = stokMin;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditVarianModal() {
        const modal = document.getElementById('modalEditVarian');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openHapusVarianModal(id, sku) {
        const modal = document.getElementById('modalHapusVarian');
        const form = document.getElementById('formHapusVarian');
        form.action = "{{ url('/varian-produk') }}/" + id;
        document.getElementById('skuHapusPlaceholder').textContent = `"${sku}"`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeHapusVarianModal() {
        const modal = document.getElementById('modalHapusVarian');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    window.addEventListener('click', (e) => {
        ['modalTambahVarian', 'modalEditVarian', 'modalHapusVarian'].forEach(id => {
            const modal = document.getElementById(id);
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });

    // Live search
    const searchVarianInput = document.getElementById('searchVarian');
    if (searchVarianInput) {
        searchVarianInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.row-varian');
            let visibleCount = 0;

            rows.forEach(row => {
                const sku = row.getAttribute('data-sku') || '';
                const produk = row.getAttribute('data-produk') || '';
                const ukuran = row.getAttribute('data-ukuran') || '';
                const warna = row.getAttribute('data-warna') || '';
                const id = row.getAttribute('data-id') || '';

                if (sku.includes(query) || produk.includes(query) || ukuran.includes(query) || warna.includes(query) || id.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const labelShowing = document.getElementById('labelShowingVarian');
            if (labelShowing) {
                labelShowing.textContent = visibleCount > 0 ? `1–${visibleCount}` : '0';
            }
        });
    }

    // Export CSV
    function exportVarianCSV() {
        const rows = document.querySelectorAll('#varianTable tbody tr.row-varian');
        let csvContent = "data:text/csv;charset=utf-8,No,ID Varian,SKU,Nama Produk,Ukuran,Warna,Stok,Stok Minimum,Created At\n";

        let index = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const id = row.getAttribute('data-id');
                const sku = row.querySelector('td:nth-child(2) .font-semibold').textContent.trim();
                const produk = row.querySelector('td:nth-child(3)').textContent.trim();
                const ukuran = row.querySelector('td:nth-child(4)').textContent.trim();
                const warna = row.querySelector('td:nth-child(5)').textContent.trim();
                const stok = row.querySelector('td:nth-child(6)').textContent.trim();
                const stokMin = row.querySelector('td:nth-child(7)').textContent.trim();
                const createdAt = row.querySelector('td:nth-child(8)').textContent.trim();

                csvContent += `"${index}","${id}","${sku}","${produk.replace(/"/g, '""')}","${ukuran}","${warna}","${stok}","${stokMin}","${createdAt}"\n`;
                index++;
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `varian_produk_kasiraja_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
