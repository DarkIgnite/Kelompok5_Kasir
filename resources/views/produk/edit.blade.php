@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page_title', 'Edit Produk')

@section('content')
<div class="space-y-6">

    @if(isset($errors) && $errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- CARD FORM EDIT PRODUK --}}
    <div class="bg-white rounded-2xl border border-neutral-200/90 p-8 shadow-2xs">
        <form action="{{ route('produk.update', $produk->id_produk) }}" method="POST" enctype="multipart/form-data" id="formEditProduk" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="hapus_foto" id="inputHapusFoto" value="0">

            {{-- 1. FOTO PRODUK --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-800 mb-2">Foto Produk</label>
                <div class="flex items-center gap-5">
                    <!-- Preview Box -->
                    <div id="fotoPreviewBox" class="w-44 h-28 rounded-xl bg-neutral-100/80 border border-neutral-200 flex flex-col items-center justify-center text-neutral-400 gap-1.5 overflow-hidden shrink-0 relative">
                        @php
                            $hasGambar = $produk->gambar && file_exists(public_path($produk->gambar));
                        @endphp
                        <div id="emptyFotoPlaceholder" class="flex flex-col items-center justify-center gap-1.5 {{ $hasGambar ? 'hidden' : '' }}">
                            <svg class="w-7 h-7 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span class="text-xs text-neutral-400 font-medium">Belum ada foto</span>
                        </div>
                        <img 
                            id="imgPreview" 
                            src="{{ $hasGambar ? asset($produk->gambar) : '' }}" 
                            alt="{{ $produk->nama_produk }}" 
                            class="{{ $hasGambar ? '' : 'hidden' }} w-full h-full object-contain p-2"
                        >
                    </div>

                    <!-- Upload Controls -->
                    <div class="space-y-2">
                        <div id="labelNamaFile" class="text-xs text-neutral-600 font-medium">
                            {{ $hasGambar ? basename($produk->gambar) : 'Belum ada file dipilih' }}
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="file" name="foto" id="inputFoto" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="handleFotoChange(this)">
                            <button 
                                type="button" 
                                onclick="document.getElementById('inputFoto').click()" 
                                class="px-3.5 py-1.5 rounded-lg border border-neutral-300 bg-white text-xs font-semibold text-neutral-700 hover:bg-neutral-50 transition shadow-2xs cursor-pointer"
                            >
                                Ganti Foto
                            </button>
                            <button 
                                type="button" 
                                onclick="hapusFoto()" 
                                class="px-3.5 py-1.5 rounded-lg border border-red-200 bg-white text-xs font-semibold text-red-500 hover:bg-red-50 transition shadow-2xs cursor-pointer"
                            >
                                Hapus
                            </button>
                        </div>
                        <p class="text-[11px] text-neutral-400">
                            Format JPG, PNG, atau WEBP. Maksimal 2 MB.
                        </p>
                    </div>
                </div>
            </div>

            {{-- GRID FIELDS --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 pt-2">
                
                {{-- ROW 1: Nama Produk & Status --}}
                <div>
                    <label for="nama_produk" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Nama Produk <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nama_produk" 
                        id="nama_produk" 
                        maxlength="100" 
                        value="{{ old('nama_produk', $produk->nama_produk) }}"
                        placeholder="Masukkan nama produk" 
                        required 
                        class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                        oninput="document.getElementById('namaCharCount').textContent = this.value.length"
                    >
                    <div class="text-right text-[11px] text-neutral-400 mt-1">
                        <span id="namaCharCount">{{ strlen(old('nama_produk', $produk->nama_produk)) }}</span>/100
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            name="status" 
                            id="status" 
                            required 
                            class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                        >
                            <option value="aktif" {{ old('status', $produk->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status', $produk->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- ROW : Merek & Kategori --}}
                <div>
                    <label for="merek" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Merek <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="merek" 
                        id="merek" 
                        maxlength="50" 
                        value="{{ old('merek', $produk->merek) }}"
                        placeholder="Masukkan merek produk" 
                        required 
                        class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-900 transition-colors"
                        oninput="document.getElementById('merekCharCount').textContent = this.value.length"
                    >
                    <div class="text-right text-[11px] text-neutral-400 mt-1">
                        <span id="merekCharCount">{{ strlen(old('merek', $produk->merek)) }}</span>/50
                    </div>
                </div>

                <div>
                    <label for="id_kategori" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            name="id_kategori" 
                            id="id_kategori" 
                            required 
                            class="w-full h-11 px-4 pr-10 rounded-xl border border-neutral-300 text-sm text-neutral-900 appearance-none bg-white focus:outline-none focus:border-neutral-900 transition-colors cursor-pointer"
                        >
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->id_kategori }}" {{ old('id_kategori', $produk->id_kategori) == $kat->id_kategori ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-neutral-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- ROW : Harga Beli & Harga Jual --}}
                <div>
                    <label for="harga_beli" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Harga Beli <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="harga_beli" 
                            id="harga_beli" 
                            value="Rp {{ number_format(old('harga_beli', $produk->harga_beli), 0, ',', '.') }}" 
                            required 
                            class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 transition-colors"
                            oninput="formatRupiahInput(this)"
                        >
                    </div>
                </div>

                <div>
                    <label for="harga_jual" class="block text-xs font-semibold text-neutral-800 mb-1.5">
                        Harga Jual <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="harga_jual" 
                            id="harga_jual" 
                            value="Rp {{ number_format(old('harga_jual', $produk->harga_jual), 0, ',', '.') }}" 
                            required 
                            class="w-full h-11 px-4 rounded-xl border border-neutral-300 text-sm text-neutral-900 focus:outline-none focus:border-neutral-900 transition-colors"
                            oninput="formatRupiahInput(this)"
                        >
                    </div>
                </div>

            </div>

            {{-- FOOTER ACTIONS --}}
            <div class="border-t border-neutral-200/80 pt-6 mt-8 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <!-- Left: Hapus Button -->
                <div>
                    <button 
                        type="button" 
                        onclick="document.getElementById('modalHapusProdukEdit').classList.remove('hidden'); document.getElementById('modalHapusProdukEdit').classList.add('flex');"
                        class="px-4 py-2.5 rounded-xl border border-red-200 bg-white text-xs font-semibold text-red-500 hover:bg-red-50 transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                        <span>Hapus Produk</span>
                    </button>
                </div>

                <!-- Right: Batal & Simpan Perubahan -->
                <div class="flex items-center justify-end gap-3">
                    <a 
                        href="{{ route('produk.index') }}" 
                        class="px-5 py-2.5 rounded-xl border border-neutral-300 bg-white text-sm font-semibold text-neutral-700 hover:bg-neutral-50 transition cursor-pointer"
                    >
                        Batal
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-black text-white text-sm font-semibold hover:bg-neutral-800 transition shadow-sm cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

{{-- MODAL KONFIRMASI HAPUS PRODUK DARI EDIT --}}
<div id="modalHapusProdukEdit" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-xs hidden items-center justify-center p-4">
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
            Apakah Anda yakin ingin menghapus produk <span class="font-bold text-neutral-800">"{{ $produk->nama_produk }}"</span>?
        </p>

        <form action="{{ route('produk.destroy', $produk->id_produk) }}" method="POST" class="flex items-center justify-center gap-2.5">
            @csrf
            @method('DELETE')
            <button 
                type="button" 
                onclick="document.getElementById('modalHapusProdukEdit').classList.add('hidden'); document.getElementById('modalHapusProdukEdit').classList.remove('flex');" 
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
    function handleFotoChange(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('emptyFotoPlaceholder').classList.add('hidden');
                const img = document.getElementById('imgPreview');
                img.src = e.target.result;
                img.classList.remove('hidden');
                document.getElementById('labelNamaFile').textContent = file.name;
                document.getElementById('inputHapusFoto').value = '0';
            }
            reader.readAsDataURL(file);
        }
    }

    function hapusFoto() {
        const input = document.getElementById('inputFoto');
        input.value = '';
        document.getElementById('imgPreview').src = '';
        document.getElementById('imgPreview').classList.add('hidden');
        document.getElementById('emptyFotoPlaceholder').classList.remove('hidden');
        document.getElementById('labelNamaFile').textContent = 'Belum ada file dipilih';
        document.getElementById('inputHapusFoto').value = '1';
    }

    function formatRupiahInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value === '') {
            input.value = 'Rp 0';
            return;
        }
        let number = parseInt(value, 10);
        input.value = 'Rp ' + number.toLocaleString('id-ID');
    }
</script>
@endpush
