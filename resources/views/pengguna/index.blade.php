@extends('layouts.app')

@section('title', 'List User Sistem')
@section('page_title', 'List User Sistem')

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
                id="searchUser" 
                placeholder="Cari Nama User" 
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
                <div id="menuDropdownFilter" class="hidden absolute right-0 mt-1 w-52 bg-white border border-neutral-200 rounded-xl shadow-xl z-30 p-3 text-xs space-y-3">
                    <div>
                        <span class="font-bold text-neutral-800 uppercase tracking-wider block mb-1 text-[10px]">Status</span>
                        <div class="space-y-1">
                            <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ !request('status') ? 'font-bold bg-neutral-100' : '' }}">Semua Status</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'aktif']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('status') === 'aktif' ? 'font-bold bg-neutral-100 text-emerald-600' : '' }}">Aktif</a>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'nonaktif']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('status') === 'nonaktif' ? 'font-bold bg-neutral-100 text-red-600' : '' }}">Nonaktif</a>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-neutral-100">
                        <span class="font-bold text-neutral-800 uppercase tracking-wider block mb-1 text-[10px]">Jabatan</span>
                        <div class="space-y-1">
                            <a href="{{ request()->fullUrlWithQuery(['role' => null]) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ !request('role') ? 'font-bold bg-neutral-100' : '' }}">Semua Jabatan</a>
                            <a href="{{ request()->fullUrlWithQuery(['role' => 'Owner']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('role') === 'Owner' ? 'font-bold bg-neutral-100' : '' }}">Owner</a>
                            <a href="{{ request()->fullUrlWithQuery(['role' => 'Manager']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('role') === 'Manager' ? 'font-bold bg-neutral-100' : '' }}">Manager</a>
                            <a href="{{ request()->fullUrlWithQuery(['role' => 'Kasir']) }}" class="block px-2.5 py-1.5 rounded-lg text-neutral-700 hover:bg-neutral-100 {{ request('role') === 'Kasir' ? 'font-bold bg-neutral-100' : '' }}">Kasir</a>
                        </div>
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
                onclick="exportUserCSV()" 
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
                onclick="openTambahUserModal()" 
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
            <table class="w-full text-left border-collapse" id="userTable">
                <thead>
                    <tr class="border-b border-neutral-100">
                        <th class="py-4 pl-6 pr-2 text-xs font-bold text-neutral-800 w-16 text-center">No.</th>
                        <th class="py-4 pl-2 pr-6 text-xs font-bold text-neutral-800">Nama</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Jabatan</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">No. Telepon</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Status</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800">Created at</th>
                        <th class="py-4 px-6 text-xs font-bold text-neutral-800 w-48 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100" id="userTbody">
                    @php
                        $currentUser = Auth::user();
                        $isCurrentOwner = $currentUser ? $currentUser->isOwner() : false;
                    @endphp

                    @forelse($pengguna as $item)
                        @php
                            $isCurrent = ($currentUser && $currentUser->id_pengguna === $item->id_pengguna);
                            $canEdit = $currentUser ? $currentUser->canEditUser($item) : false;
                            $canDelete = $currentUser ? $currentUser->canDeleteUser($item) : false;

                            // Display Name with Suffix
                            $displayName = $item->nama_lengkap ?? $item->username;
                            $suffix = '';
                            if ($isCurrent) {
                                $suffix = ' (Current)';
                            } elseif ($item->isOwner() || $item->jabatan === 'Owner') {
                                $suffix = ' (Admin)';
                            }
                        @endphp
                        <tr class="hover:bg-neutral-50/60 transition-colors row-user {{ $isCurrent ? 'bg-neutral-50/40' : '' }}" 
                            data-nama="{{ strtolower($item->nama_lengkap . ' ' . $item->username) }}" 
                            data-jabatan="{{ strtolower($item->jabatan) }}"
                            data-telepon="{{ $item->no_telepon ?? '' }}"
                            data-status="{{ strtolower($item->status) }}"
                            data-id="{{ $item->id_pengguna }}"
                        >
                            {{-- No. --}}
                            <td class="py-4 pl-6 pr-2 text-sm font-medium text-neutral-800 align-middle text-center">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Nama --}}
                            <td class="py-4 pl-2 pr-6 align-middle">
                                <div class="text-sm font-semibold text-neutral-900 leading-tight">
                                    {{ $displayName }}<span class="text-neutral-500 font-medium">{{ $suffix }}</span>
                                </div>
                                <div class="text-[11px] text-neutral-400 font-medium mt-0.5">
                                    ID : {{ $item->id_pengguna }}
                                </div>
                            </td>

                            {{-- Jabatan --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->jabatan }}
                            </td>

                            {{-- No. Telepon --}}
                            <td class="py-4 px-6 text-sm font-medium text-neutral-700 align-middle">
                                {{ $item->no_telepon ?: '-' }}
                            </td>

                            {{-- Status --}}
                            <td class="py-4 px-6 text-xs font-semibold align-middle">
                                @if(strtolower($item->status) === 'aktif')
                                    <span class="text-emerald-500">Aktif</span>
                                @else
                                    <span class="text-red-500">Nonaktif</span>
                                @endif
                            </td>

                            {{-- 6. Created at --}}
                            <td class="py-4 px-6 text-sm font-normal text-neutral-700 align-middle whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->created_at)->locale('id')->isoFormat('D MMMM Y') }}
                            </td>

                            {{-- 7. Aksi --}}
                            <td class="py-4 px-6 align-middle">
                                <div class="flex items-center gap-2">
                                    {{-- Edit Button (sesuai hierarki: Owner > Manager > Kasir) --}}
                                    @if($canEdit)
                                    <button 
                                        type="button" 
                                        onclick="openEditUserModal({{ $item->id_pengguna }}, '{{ addslashes($item->nama_lengkap ?? $item->username) }}', '{{ $item->jabatan }}', '{{ $item->status }}', '{{ addslashes($item->no_telepon ?? '') }}')" 
                                        class="px-3 py-1.5 rounded-lg border border-neutral-200 bg-white text-xs font-semibold text-neutral-700 hover:bg-neutral-50 hover:border-neutral-300 transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer"
                                    >
                                        <svg class="w-3.5 h-3.5 text-neutral-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    @else
                                        <button 
                                            type="button" 
                                            disabled 
                                            class="px-3 py-1.5 rounded-lg border border-neutral-200 bg-neutral-50 text-xs font-semibold text-neutral-400 flex items-center gap-1.5 cursor-not-allowed opacity-60"
                                            title="Anda tidak berhak mengedit user ini"
                                        >
                                            <svg class="w-3.5 h-3.5 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                            <span>Edit</span>
                                        </button>
                                    @endif

                                    {{-- Hapus Button (bukan diri sendiri & sesuai hierarki) --}}
                                    @if($canDelete)
                                        <button 
                                            type="button" 
                                            onclick="openHapusUserModal({{ $item->id_pengguna }}, '{{ addslashes($item->nama_lengkap ?? $item->username) }}')" 
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
                                    @else
                                        {{-- Disabled Hapus Button untuk akun sendiri --}}
                                        <button 
                                            type="button" 
                                            disabled 
                                            class="px-3 py-1.5 rounded-lg border border-neutral-200 bg-neutral-50 text-xs font-semibold text-neutral-400 flex items-center gap-1.5 cursor-not-allowed opacity-60"
                                            title="{{ $isCurrent ? 'Tidak dapat menghapus akun sendiri' : 'Anda tidak berhak menghapus user ini' }}"
                                        >
                                            <svg class="w-3.5 h-3.5 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                            <span>Hapus</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-sm text-neutral-400">
                                Belum ada data user. Klik tombol <strong>+ Tambah Data</strong> di atas untuk membuat user baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABLE FOOTER / PAGINATION --}}
        <div class="px-6 py-4 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs font-medium text-neutral-600">
                Menampilkan <span id="labelShowingUser">1–{{ $pengguna->count() }}</span> dari <span id="labelTotalUser">{{ method_exists($pengguna, 'total') ? $pengguna->total() : $pengguna->count() }}</span> user
            </div>

            <div class="flex items-center gap-1">
                @if($pengguna instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    @if ($pengguna->onFirstPage())
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-300 cursor-not-allowed text-xs">&lsaquo;</span>
                    @else
                        <a href="{{ $pengguna->previousPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&lsaquo;</a>
                    @endif

                    @foreach ($pengguna->getUrlRange(1, $pengguna->lastPage()) as $page => $url)
                        @if ($page == $pengguna->currentPage())
                            <span class="w-7 h-7 rounded-lg bg-black text-white flex items-center justify-center text-xs font-bold">{{ $page }}</span>
                        @elseif($page <= 4 || $page == $pengguna->lastPage() || abs($page - $pengguna->currentPage()) <= 1)
                            <a href="{{ $url }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs font-medium">{{ $page }}</a>
                        @elseif($page == 5)
                            <span class="w-7 h-7 flex items-center justify-center text-neutral-400 text-xs">...</span>
                        @endif
                    @endforeach

                    @if ($pengguna->hasMorePages())
                        <a href="{{ $pengguna->nextPageUrl() }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-neutral-700 hover:bg-neutral-100 transition text-xs">&rsaquo;</a>
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

{{-- MODAL TAMBAH USER (CRUD_User_Sistem_Tambah.png) --}}
<div id="modalTambahUser" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-neutral-100">
            <h3 class="text-base font-bold text-neutral-900 tracking-tight">Tambah User</h3>
            <button type="button" onclick="closeTambahUserModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('pengguna.store') }}" method="POST" id="formTambahUser" class="mt-5 space-y-4">
            @csrf
            
            {{-- Nama User --}}
            <div>
                <label for="nama_user_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Nama User <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="nama_user" 
                    id="nama_user_tambah" 
                    placeholder="Masukkan nama user" 
                    required 
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                >
            </div>

            {{-- Password with Eye Toggle --}}
            <div>
                <label for="password_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="password_tambah" 
                        placeholder="Masukkan password" 
                        required 
                        class="w-full h-11 px-4 pr-11 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                    >
                    <button 
                        type="button" 
                        onclick="togglePasswordVisibility('password_tambah', 'eye_tambah')" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-neutral-500 hover:text-neutral-800 cursor-pointer"
                    >
                        <svg id="eye_tambah" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Jabatan & Status --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="jabatan_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            name="jabatan" 
                            id="jabatan_tambah" 
                            required 
                            class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                        >
                            <option value="" disabled selected>Pilih Jabatan</option>
                            @if(Auth::user()?->isOwner())
                            <option value="Owner">Owner</option>
                            @endif
                            <option value="Manager">Manager</option>
                            <option value="Kasir">Kasir</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="status_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            name="status" 
                            id="status_tambah" 
                            required 
                            class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                        >
                            <option value="aktif" selected>Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- No. Telepon --}}
            <div>
                <label for="no_telepon_tambah" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    No. Telepon
                </label>
                <input 
                    type="text" 
                    name="no_telepon" 
                    id="no_telepon_tambah" 
                    placeholder="Masukkan no. telepon" 
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                >
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeTambahUserModal()" 
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

{{-- MODAL EDIT USER (CRUD_User_Sistem_Edit_Diri_Sendiri.png & CRUD_User_Sistem_Edit_Bawahani.png) --}}
<div id="modalEditUser" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-neutral-100">
            <h3 class="text-base font-bold text-neutral-900 tracking-tight">Edit User</h3>
            <button type="button" onclick="closeEditUserModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="" method="POST" id="formEditUser" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            
            {{-- Nama User --}}
            <div>
                <label for="nama_user_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Nama User <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="nama_user" 
                    id="nama_user_edit" 
                    required 
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 transition-colors"
                >
            </div>

            {{-- Password with Eye Toggle --}}
            <div>
                <label for="password_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        id="password_edit" 
                        placeholder="Masukkan password baru" 
                        class="w-full h-11 px-4 pr-11 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                    >
                    <button 
                        type="button" 
                        onclick="togglePasswordVisibility('password_edit', 'eye_edit')" 
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-neutral-500 hover:text-neutral-800 cursor-pointer"
                    >
                        <svg id="eye_edit" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Jabatan & Status (2 Columns) --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="jabatan_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            name="jabatan" 
                            id="jabatan_edit" 
                            required 
                            class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                        >
                            @if(Auth::user()?->isOwner())
                            <option value="Owner">Owner</option>
                            @endif
                            <option value="Manager">Manager</option>
                            <option value="Kasir">Kasir</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="status_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            name="status" 
                            id="status_edit" 
                            required 
                            class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                        >
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- No. Telepon --}}
            <div>
                <label for="no_telepon_edit" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                    No. Telepon
                </label>
                <input 
                    type="text" 
                    name="no_telepon" 
                    id="no_telepon_edit" 
                    placeholder="Masukkan no. telepon" 
                    class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 transition-colors"
                >
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeEditUserModal()" 
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

{{-- MODAL HAPUS USER --}}
<div id="modalHapusUser" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 relative animate-in fade-in zoom-in-95 duration-150 text-center">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-500 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 class="text-base font-bold text-neutral-900 mb-1">Hapus User?</h3>
        <p class="text-xs text-neutral-500 mb-6">
            Apakah Anda yakin ingin menghapus user <span id="namaHapusUserPlaceholder" class="font-bold text-neutral-800"></span>? Data yang dihapus tidak dapat dikembalikan.
        </p>

        <form action="" method="POST" id="formHapusUser" class="flex items-center justify-center gap-2.5">
            @csrf
            @method('DELETE')
            <button 
                type="button" 
                onclick="closeHapusUserModal()" 
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

    // 3. Toggle Password Visibility
    function togglePasswordVisibility(inputId, eyeIconId) {
        const input = document.getElementById(inputId);
        const eye = document.getElementById(eyeIconId);
        if (input.type === 'password') {
            input.type = 'text';
            eye.innerHTML = `
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            `;
        } else {
            input.type = 'password';
            eye.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }
    }

    // 4. Modal Tambah Handlers
    function openTambahUserModal() {
        const modal = document.getElementById('modalTambahUser');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        const input = document.getElementById('nama_user_tambah');
        input.value = '';
        document.getElementById('password_tambah').value = '';
        document.getElementById('no_telepon_tambah').value = '';
        setTimeout(() => input.focus(), 50);
    }

    function closeTambahUserModal() {
        const modal = document.getElementById('modalTambahUser');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 5. Modal Edit Handlers
    function openEditUserModal(id, nama, jabatan, status, noTelepon) {
        const modal = document.getElementById('modalEditUser');
        const form = document.getElementById('formEditUser');
        form.action = "{{ url('/pengguna') }}/" + id;

        document.getElementById('nama_user_edit').value = nama;
        document.getElementById('password_edit').value = '';
        document.getElementById('status_edit').value = status.toLowerCase();
        document.getElementById('no_telepon_edit').value = noTelepon;
        document.getElementById('jabatan_edit').value = jabatan;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => document.getElementById('nama_user_edit').focus(), 50);
    }

    function closeEditUserModal() {
        const modal = document.getElementById('modalEditUser');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 6. Modal Hapus Handlers
    function openHapusUserModal(id, nama) {
        const modal = document.getElementById('modalHapusUser');
        const form = document.getElementById('formHapusUser');
        form.action = "{{ url('/pengguna') }}/" + id;
        document.getElementById('namaHapusUserPlaceholder').textContent = `"${nama}"`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeHapusUserModal() {
        const modal = document.getElementById('modalHapusUser');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Backdrop Click
    window.addEventListener('click', (e) => {
        ['modalTambahUser', 'modalEditUser', 'modalHapusUser'].forEach(id => {
            const modal = document.getElementById(id);
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    });

    // 7. Live Client-Side Search Filter
    const searchUser = document.getElementById('searchUser');
    if (searchUser) {
        searchUser.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.row-user');
            let visibleCount = 0;

            rows.forEach(row => {
                const nama = row.getAttribute('data-nama') || '';
                const jabatan = row.getAttribute('data-jabatan') || '';
                const telepon = row.getAttribute('data-telepon') || '';
                const id = row.getAttribute('data-id') || '';

                if (nama.includes(query) || jabatan.includes(query) || telepon.includes(query) || id.includes(query)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const labelShowing = document.getElementById('labelShowingUser');
            if (labelShowing) {
                labelShowing.textContent = visibleCount > 0 ? `1–${visibleCount}` : '0';
            }
        });
    }

    // 8. Export to CSV Function
    function exportUserCSV() {
        const rows = document.querySelectorAll('#userTable tbody tr.row-user');
        let csvContent = "data:text/csv;charset=utf-8,No,ID User,Nama,Jabatan,No. Telepon,Status,Created At\n";

        let index = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const id = row.getAttribute('data-id');
                const nama = row.querySelector('td:nth-child(2) .font-semibold').textContent.trim();
                const jabatan = row.querySelector('td:nth-child(3)').textContent.trim();
                const telp = row.querySelector('td:nth-child(4)').textContent.trim();
                const status = row.querySelector('td:nth-child(5)').textContent.trim();
                const createdAt = row.querySelector('td:nth-child(6)').textContent.trim();

                csvContent += `"${index}","${id}","${nama.replace(/"/g, '""')}","${jabatan}","${telp}","${status}","${createdAt}"\n`;
                index++;
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `user_sistem_kasiraja_${new Date().toISOString().slice(0, 10)}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
