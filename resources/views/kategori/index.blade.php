@extends('layouts.app')

@section('title', 'List Kategori')
@section('page_title', 'List Kategori')

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
                id="searchKategori" 
                placeholder="Cari Nama Kategori" 
                value="{{ request('search') }}"
                class="w-full h-11 pl-10 pr-4 rounded-xl bg-white border border-neutral-300 text-sm text-neutral-800 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors shadow-2xs"
            >
        </div>

        <!-- Right Controls: Show Entries, Export CSV, Tambah Data -->
        <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap justify-end">
            <!-- Show 100 Dropdown -->
            <div class="relative" id="dropdownShowWrapper">
                <button 
                    type="button" 
                    id="btnDropdownShow" 
                    class="h-11 px-4 bg-white border border-neutral-300 rounded-xl flex items-center gap-2 text-sm text-neutral-700 hover:bg-neutral-50 transition shadow-2xs"
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
                onclick="exportKategoriCSV()" 
                class="h-11 px-4 bg-white border border-neutral-300 rounded-xl flex items-center gap-2 text-sm font-semibold text-neutral-800 hover:bg-neutral-50 transition shadow-2xs cursor-pointer"
            >
                <!-- Export Box Icon -->
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
                onclick="openTambahModal()" 
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
            <table class="w-full text-left border-collapse" id="kategoriTable">
                <thead>
                    <tr class="border-b border-neutral-100">
                        <th class="py-4 pl-6 pr-2 text-xs font-bold text-neutral-800 w-16 text-center">No.</th>
                        <th class="py-4 pl-2 pr-6 text-xs font-bold text-neutral-800">Nama Kategori</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Total Produk</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800 w-48 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100" id="kategoriTbody">
                    @forelse($kategori as $item)
                        <tr class="hover:bg-neutral-50/60 transition-colors row-kategori" data-nama="{{ strtolower($item->nama_kategori) }}" data-id="{{ $item->id_kategori }}">
                            <td class="py-4 pl-6 pr-2 text-sm font-medium text-neutral-800 align-middle text-center">
                                {{ $loop->iteration }}
                            </td>
                            <td class="py-4 pl-2 pr-6 align-middle">
                                <div class="text-sm font-semibold text-neutral-900 leading-tight">
                                    {{ $item->nama_kategori }}
                                </div>
                                <div class="text-[11px] text-neutral-400 font-medium mt-0.5">
                                    ID : {{ $item->id_kategori }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->produk_count ?? $item->produk->count() }}
                            </td>
                            <td class="py-4 px-6 align-middle">
                                <div class="flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ $item->id_kategori }}, '{{ addslashes($item->nama_kategori) }}')" 
                                        class="px-3 py-1.5 rounded-lg border border-neutral-200 bg-white text-xs font-semibold text-neutral-700 hover:bg-neutral-50 hover:border-neutral-300 transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                    >
                                        <!-- Pencil / Edit Icon -->
                                        <svg class="w-3.5 h-3.5 text-neutral-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Hapus Button -->
                                    <button 
                                        type="button" 
                                        onclick="openHapusModal({{ $item->id_kategori }}, '{{ addslashes($item->nama_kategori) }}')" 
                                        class="px-3 py-1.5 rounded-lg border border-red-200 bg-white text-xs font-semibold text-red-500 hover:bg-red-50 hover:border-red-300 transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                    >
                                        <!-- Trash Icon -->
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
                        <tr id="emptyRow">
                            <td colspan="4" class="py-12 text-center text-sm text-neutral-400">
                                Belum ada data kategori. Klik tombol <strong>+ Tambah Data</strong> di atas untuk membuat kategori baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABLE FOOTER / PAGINATION --}}
        <div class="px-6 py-4 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs font-medium text-neutral-600">
                Menampilkan <span id="labelShowing">1–{{ $kategori->count() }}</span> dari <span id="labelTotal">{{ method_exists($kategori, 'total') ? $kategori->total() : $kategori->count() }}</span> kategori
            </div>

            <div class="flex items-center gap-1">
                @if($kategori instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{-- Previous Page Link --}}
                    @if ($kategori->onFirstPage())
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-300 cursor-not-allowed text-xs">&lsaquo;</span>
                    @else
                        <a href="{{ $kategori->previousPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&lsaquo;</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($kategori->getUrlRange(1, $kategori->lastPage()) as $page => $url)
                        @if ($page == $kategori->currentPage())
                            <span class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center text-xs font-bold">{{ $page }}</span>
                        @elseif($page <= 4 || $page == $kategori->lastPage() || abs($page - $kategori->currentPage()) <= 1)
                            <a href="{{ $url }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs font-medium">{{ $page }}</a>
                        @elseif($page == 5)
                            <span class="w-7 h-7 flex items-center justify-center text-neutral-400 text-xs">...</span>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($kategori->hasMorePages())
                        <a href="{{ $kategori->nextPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&rsaquo;</a>
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

{{-- MODAL : TAMBAH KATEGORI --}}
<div id="modalTambah" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-neutral-100">
            <h3 class="text-base font-bold text-neutral-900 tracking-tight">Tambah Kategori</h3>
            <button type="button" onclick="closeTambahModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('kategori.store') }}" method="POST" id="formTambahKategori" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="inputNamaKategoriTambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="inputNamaKategoriTambah" 
                    name="nama_kategori" 
                    maxlength="50" 
                    placeholder="Masukkan nama kategori" 
                    required
                    autocomplete="off"
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                    oninput="document.getElementById('countTambah').textContent = this.value.length"
                >
                <div class="text-right text-[11px] text-neutral-400 mt-1">
                    <span id="countTambah">0</span>/50
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeTambahModal()" 
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

{{-- MODAL : EDIT KATEGORI  --}}
<div id="modalEdit" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-neutral-100">
            <h3 class="text-base font-bold text-neutral-900 tracking-tight">Edit Kategori</h3>
            <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="" method="POST" id="formEditKategori" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="inputNamaKategoriEdit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="inputNamaKategoriEdit" 
                    name="nama_kategori" 
                    maxlength="50" 
                    required
                    autocomplete="off"
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                    oninput="document.getElementById('countEdit').textContent = this.value.length"
                >
                <div class="text-right text-[11px] text-neutral-400 mt-1">
                    <span id="countEdit">0</span>/50
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeEditModal()" 
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

{{-- MODAL : KONFIRMASI HAPUS --}}
<div id="modalHapus" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative animate-in fade-in zoom-in-95 duration-150 text-center">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 class="text-base font-bold text-neutral-900 mb-1">Hapus Kategori?</h3>
        <p class="text-xs text-neutral-500 mb-6">
            Apakah Anda yakin ingin menghapus kategori <span id="namaHapusPlaceholder" class="font-bold text-neutral-800"></span>? Data yang dihapus tidak dapat dikembalikan.
        </p>

        <form action="" method="POST" id="formHapusKategori" class="flex items-center justify-center gap-2.5">
            @csrf
            @method('DELETE')
            <button 
                type="button" 
                onclick="closeHapusModal()" 
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
    // Dropdown Show Menu
    const btnDropdownShow = document.getElementById('btnDropdownShow');
    const menuDropdownShow = document.getElementById('menuDropdownShow');
    if (btnDropdownShow && menuDropdownShow) {
        btnDropdownShow.addEventListener('click', (e) => {
            e.stopPropagation();
            menuDropdownShow.classList.toggle('hidden');
        });
        document.addEventListener('click', () => {
            menuDropdownShow.classList.add('hidden');
        });
    }

    // Modal Tambah Handlers
    function openTambahModal() {
        const modal = document.getElementById('modalTambah');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const input = document.getElementById('inputNamaKategoriTambah');
        input.value = '';
        document.getElementById('countTambah').textContent = '0';
        setTimeout(() => input.focus(), 50);
    }

    function closeTambahModal() {
        const modal = document.getElementById('modalTambah');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Edit Handlers
    function openEditModal(id, nama) {
        const modal = document.getElementById('modalEdit');
        const form = document.getElementById('formEditKategori');
        const input = document.getElementById('inputNamaKategoriEdit');
        const count = document.getElementById('countEdit');

        form.action = "{{ url('/kategori') }}/" + id;
        input.value = nama;
        count.textContent = nama.length;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => input.focus(), 50);
    }

    function closeEditModal() {
        const modal = document.getElementById('modalEdit');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Modal Hapus Handlers
    function openHapusModal(id, nama) {
        const modal = document.getElementById('modalHapus');
        const form = document.getElementById('formHapusKategori');
        form.action = "{{ url('/kategori') }}/" + id;
        document.getElementById('namaHapusPlaceholder').textContent = `"${nama}"`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeHapusModal() {
        const modal = document.getElementById('modalHapus');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Close on Backdrop Click
    window.addEventListener('click', (e) => {
        ['modalTambah', 'modalEdit', 'modalHapus'].forEach(id => {
            const modal = document.getElementById(id);
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });

    // Client-Side Live Filter Search
    const searchInput = document.getElementById('searchKategori');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.row-kategori');
            let visibleCount = 0;

            rows.forEach(row => {
                const nama = row.getAttribute('data-nama') || '';
                const id = row.getAttribute('data-id') || '';
                if (nama.includes(query) || id.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const labelShowing = document.getElementById('labelShowing');
            if (labelShowing) {
                labelShowing.textContent = visibleCount > 0 ? `1–${visibleCount}` : '0';
            }
        });
    }

    // Export to CSV Function
    function exportKategoriCSV() {
        const rows = document.querySelectorAll('#kategoriTable tbody tr.row-kategori');
        let csvContent = "data:text/csv;charset=utf-8,No,ID Kategori,Nama Kategori,Total Produk\n";

        let index = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const id = row.getAttribute('data-id');
                const nama = row.querySelector('td:nth-child(2) .font-semibold').textContent.trim();
                const total = row.querySelector('td:nth-child(3)').textContent.trim();
                csvContent += `"${index}","${id}","${nama.replace(/"/g, '""')}","${total}"\n`;
                index++;
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `kategori_kasiraja_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
