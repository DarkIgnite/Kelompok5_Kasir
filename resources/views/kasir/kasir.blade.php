<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kasir POS - KasirAja</title>
    <!-- Logo KasirAja di tab browser -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts: Inter (sama dengan desain Figma) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        :root {
            --sidebar-width: max(64px, calc(100vw / 18));
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #FFFFFF;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F8F9FA;
        }
        ::-webkit-scrollbar-thumb {
            background: #E5E7EB;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #D1D5DB;
        }

        /* Print styling khusus thermal receipt */
        @media print {
            body * {
                visibility: hidden !important;
            }
            #printableReceipt, #printableReceipt * {
                visibility: visible !important;
            }
            #printableReceipt {
                position: fixed !important;
                left: 50% !important;
                top: 20px !important;
                transform: translateX(-50%) !important;
                width: 380px !important;
                border: 1px dashed #777 !important;
                box-shadow: none !important;
                padding: 24px !important;
                background: white !important;
            }
        }
    </style>
</head>
<body class="min-h-screen text-neutral-900 antialiased selection:bg-neutral-900 selection:text-white bg-white">

    <div class="relative w-full min-h-screen flex bg-white">
        
        <!-- SIDEBAR -->
        @include('layouts.sidebar')
        @include('layouts.flash-alert')

        <!-- MAIN LAYOUT WRAPPER -->
        <div 
            class="min-h-screen flex-1 flex flex-col transition-all overflow-x-hidden"
            style="margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width));"
        >

            <!--VIEW CATALOG (Halaman Awal Kasir / POS)-->
            <div id="viewCatalog" class="flex-1 flex flex-col lg:flex-row bg-white transition-all">
                
                <!-- LEFT/CENTER AREA: PRODUCT CATALOG -->
                <section class="flex-1 min-w-0 px-[30px] pb-[30px] overflow-y-auto max-h-screen bg-white">
                    
                    <!-- TOP BAR: SEARCH & FILTER -->
                    <div class="sticky top-0 z-10 bg-white -mx-[30px] px-[30px] h-[100px] flex items-center gap-[15px] mb-[35px] shadow-[0_1px_0_rgba(0,0,0,0.1)]">
                        <!-- Search Input -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-[#A7A7A7]">
                                <svg class="w-6 h-6 text-[#A7A7A7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="productSearchInput" 
                                placeholder="Search" 
                                class="w-full h-[46px] pl-[55px] pr-4 bg-white border border-black/30 rounded-[13px] text-[15px] text-neutral-800 placeholder-[#A7A7A7] focus:outline-none focus:border-neutral-400 focus:ring-1 focus:ring-neutral-400 transition"
                            >
                        </div>

                        <!-- Filter Dropdown Button -->
                        <div class="relative">
                            <button 
                                type="button" 
                                id="filterMenuBtn" 
                                class="inline-flex items-center gap-[10px] h-[46px] px-[11px] bg-white border border-black/30 rounded-[10px] text-[15px] font-normal text-black hover:bg-neutral-50 transition cursor-pointer whitespace-nowrap"
                            >
                                <span id="currentFilterLabel">Filter to "Semua Kategori"</span>
                                <svg class="w-6 h-6 text-black transition-transform duration-200" id="filterChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div 
                                id="filterDropdown" 
                                class="hidden absolute right-0 mt-1.5 w-52 bg-white border border-neutral-200 rounded-xl shadow-lg z-30 py-1.5 divide-y divide-neutral-100"
                            >
                                <div class="py-1">
                                    <button 
                                        type="button" 
                                        class="category-filter-item w-full text-left px-3.5 py-2 text-xs font-medium text-neutral-800 hover:bg-neutral-50 flex items-center justify-between"
                                        data-category-id=""
                                        data-category-name="Semua Kategori"
                                    >
                                        <span>Semua Kategori</span>
                                    </button>
                                </div>
                                <div class="py-1">
                                    @foreach ($kategoris as $kat)
                                        <button 
                                            type="button" 
                                            class="category-filter-item w-full text-left px-3.5 py-2 text-xs font-normal text-neutral-700 hover:bg-neutral-50"
                                            data-category-id="{{ $kat->id_kategori }}"
                                            data-category-name="{{ $kat->nama_kategori }}"
                                        >
                                            {{ $kat->nama_kategori }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRODUCT CARDS GRID (3 Columns) -->
                    <div id="productGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-[34px]">
                        @forelse ($displayProduks as $p)
                            @php
                                $isFayer = str_contains(strtolower($p->nama_produk), 'fayer');
                            @endphp
                            <div 
                                class="product-card bg-white rounded-2xl border border-black/30 p-[25px] hover:shadow-md transition-shadow duration-200 flex flex-col justify-between"
                                data-product-id="{{ $p->id_produk }}"
                                data-product-name="{{ $p->nama_produk }}"
                                data-category-id="{{ $p->id_kategori }}"
                            >
                                <div>
                                    <!-- Thumbnail image -->
                                    <div class="w-full h-[124px] rounded-[5px] border border-black/20 flex items-center justify-center {{ $p->gambar_is_remote ? '' : 'p-3' }} relative overflow-hidden {{ $isFayer ? 'bg-black' : 'bg-neutral-100' }}">
                                        <img 
                                            src="{{ $p->gambar_url }}" 
                                            alt="{{ $p->nama_produk }}" 
                                            class="{{ $p->gambar_is_remote ? 'w-full h-full object-cover' : 'max-h-full max-w-full object-contain drop-shadow-sm' }} pointer-events-none"
                                            loading="lazy"
                                        >
                                    </div>

                                    <!-- Product Info -->
                                    <div class="mt-[10px] px-[2px]">
                                        <h3 class="text-base font-bold text-black leading-5">
                                            {{ $p->nama_produk }}
                                        </h3>
                                        <p class="text-sm font-semibold text-black mt-[2px] leading-5">
                                            Rp{{ number_format($p->harga_jual, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <!-- Product Meta: Warna, Ukuran, Stok -->
                                    <div class="mt-[10px] px-[2px] text-[12px] leading-[15px] text-black">
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Merek</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-black font-normal truncate">{{ $p->merek ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Warna</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-black font-normal truncate">{{ $p->warna_text ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Ukuran</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-black font-normal truncate">{{ $p->ukuran_text ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Stok</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-black font-normal">{{ $p->total_stok ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add to Order Button -->
                                <div class="mt-5">
                                    <button 
                                        type="button" 
                                        class="btn-add-to-order w-full h-[39px] {{ $p->total_stok > 0 ? 'bg-black hover:bg-neutral-800 active:scale-[0.98] cursor-pointer' : 'bg-neutral-300 cursor-not-allowed' }} text-white text-base font-medium rounded-[10px] border border-black/20 transition duration-150 flex items-center justify-center"
                                        data-product-id="{{ $p->id_produk }}"
                                        data-product-name="{{ $p->nama_produk }}"
                                        data-product-price="{{ $p->harga_jual }}"
                                        data-product-image="{{ $p->gambar_url }}"
                                        data-is-black="{{ $isFayer ? '1' : '0' }}"
                                        data-variants='@json($p->varian_json)'
                                        {{ $p->total_stok > 0 ? '' : 'disabled' }}
                                    >
                                        {{ $p->total_stok > 0 ? 'Add to Order' : 'Stok habis' }}
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 py-16 text-center text-neutral-400">
                                Tidak ada produk ditemukan.
                            </div>
                        @endforelse
                    </div>

                    <!-- Empty State Search Filter -->
                    <div id="noResultsNotice" class="hidden py-16 text-center text-neutral-400">
                        <p class="text-sm">Tidak ada produk yang cocok dengan pencarian.</p>
                    </div>
                </section>

                <!-- RIGHT AREA: ORDER DETAILS PANEL -->
                <aside 
                    id="orderDetailsSidebar"
                    class="w-full lg:w-[383px] shrink-0 bg-white border-t lg:border-t-0 lg:border-l border-black/10 flex flex-col justify-between h-auto lg:h-screen lg:sticky lg:top-0 z-20 px-[25px] py-[35px]"
                    aria-label="Order details"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-[36px] border-b border-black/30">
                        <h2 class="text-[18px] leading-7 font-bold text-black">Order Details</h2>
                        <button 
                            type="button" 
                            id="btnCloseOrder" 
                            class="text-neutral-400 hover:text-neutral-800 text-xl font-light leading-none p-1 transition cursor-pointer select-none"
                            title="Kosongkan Pesanan"
                        >
                            &times;
                        </button>
                    </div>

                    <!-- Order Items List -->
                    <div 
                        id="cartList" 
                        class="flex-1 overflow-y-auto py-[10px] space-y-[10px] max-h-[50vh] lg:max-h-none"
                    >
                        <!-- Items rendered via JavaScript -->
                    </div>

                    <!-- Summary & Checkout Section -->
                    <div class="border-t border-black/30 bg-white pt-5 space-y-[15px]">
                        <div class="space-y-[5px] text-base">
                            <div class="flex items-center justify-between">
                                <span class="text-[#0F172A] font-normal">Total Item</span>
                                <span id="cartTotalItem" class="font-normal text-black">0 pcs</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#0F172A] font-normal">Subtotal</span>
                                <span id="cartSubtotal" class="font-normal text-black">Rp0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#0F172A] font-normal">Diskon</span>
                                <span id="cartDiskon" class="font-normal text-black">Rp0</span>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="flex items-center justify-between py-[15px] border-t border-black/30">
                            <span class="text-[18px] font-bold text-[#0F172A]">Total</span>
                            <span id="cartTotal" class="text-[20px] font-bold text-[#0F172A]">Rp0</span>
                        </div>

                        <!--Continue Button-->
                        <button 
                            type="button" 
                            id="btnContinueOrder" 
                            class="w-full h-[59px] bg-black hover:bg-neutral-800 active:scale-[0.98] text-white text-2xl font-medium rounded-[10px] transition duration-150 flex items-center justify-center cursor-pointer"
                        >
                            Continue
                        </button>
                    </div>
                </aside>

            </div>

            <!--PAYMENT-->
            <div id="viewPayment" class="hidden flex-1 flex flex-col lg:flex-row bg-white transition-all">
                
                <!-- LEFT/CENTER AREA: METODE PEMBAYARAN -->
                <section class="flex-1 min-w-0 p-[30px] overflow-y-auto max-h-screen bg-white">
                    <h1 class="text-[32px] font-bold text-black leading-[39px] mb-[30px]">
                        Layar Pembayaran
                    </h1>

                    <!-- Payment Card Container -->
                    <div class="bg-white rounded-2xl border border-black/25 p-[25px]">
                        
                        <!-- Header Section -->
                        <h2 class="text-[20px] leading-7 font-bold text-black mb-[10px]">
                            Pilih Metode Pembayaran
                        </h2>

                        <!--Payment Method Buttons tunai, debit, qris-->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-[10px]">
                            <!--Tunai-->
                            <button 
                                type="button" 
                                class="payment-method-btn border-2 border-black bg-white rounded-2xl h-[72px] p-[15px] flex items-center gap-[15px] transition cursor-pointer text-left active-method"
                                data-method="Tunai"
                            >
                                <div class="w-6 h-6 flex items-center justify-center text-neutral-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                        <path d="M6 12h.01M18 12h.01"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-base text-black">Tunai</span>
                            </button>

                            <!--Debit-->
                            <button 
                                type="button" 
                                class="payment-method-btn border border-black/25 bg-white rounded-2xl h-[72px] p-[15px] flex items-center gap-[15px] transition cursor-pointer text-left hover:border-neutral-400"
                                data-method="Debit"
                            >
                                <div class="w-6 h-6 flex items-center justify-center text-neutral-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                                        <line x1="2" y1="10" x2="22" y2="10"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-base text-black">Debit</span>
                            </button>

                            <!--qris-->
                            <button 
                                type="button" 
                                class="payment-method-btn border border-black/25 bg-white rounded-2xl h-[72px] p-[15px] flex items-center gap-[15px] transition cursor-pointer text-left hover:border-neutral-400"
                                data-method="QRIS"
                            >
                                <div class="w-6 h-6 flex items-center justify-center text-neutral-700">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-base text-black">QRIS</span>
                            </button>
                        </div>

                        <!-- Divider line -->
                        <div class="border-b border-black/15 mt-[15px] mb-[10px]"></div>

                        <!-- Method : Tunai Section -->
                        <div id="methodTunaiContent" class="space-y-[10px]">
                            <h3 class="text-[20px] leading-7 font-bold text-black">
                                Pembayaran Tunai
                            </h3>

                            <div>
                                <label class="block text-[12px] text-black/60 font-normal mb-[10px]">
                                    Uang diterima
                                </label>
                                <input 
                                    type="text" 
                                    id="inputPaymentReceived" 
                                    placeholder="Rp 0" 
                                    class="w-full h-[46px] px-[15px] bg-white border border-black/25 rounded-[8px] text-[15px] font-semibold text-black focus:outline-none focus:border-neutral-900 transition"
                                >
                            </div>

                            <!-- Quick Amount Suggestions Pills-->
                            <div class="flex flex-wrap items-center gap-[10px]" id="quickAmountWrapper">
                                <button type="button" id="btnUangPasQuick" class="h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer">
                                    Uang pas
                                </button>
                                <button type="button" class="btn-quick-pill h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer" data-val="466050000">
                                    Rp466.050.000
                                </button>
                                <button type="button" class="btn-quick-pill h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer" data-val="466100000">
                                    Rp466.100.000
                                </button>
                                <button type="button" class="btn-quick-pill h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer" data-val="500000000">
                                    Rp500.000.000
                                </button>
                            </div>

                            <!-- Kembalian Box -->
                            <div class="bg-black/5 rounded-[8px] h-[35px] px-[10px] flex items-center justify-between">
                                <span class="text-[12px] font-normal text-black">Kembalian</span>
                                <span id="paymentKembalianText" class="text-[12px] font-semibold text-[#00A854]">Rp0</span>
                            </div>

                            <!-- Validasi Pembayaran Button -->
                            <div>
                                <button 
                                    type="button" 
                                    id="btnValidatePayment" 
                                    class="h-[35px] px-5 rounded-[10px] text-[12px] font-medium transition duration-150 bg-neutral-300 text-white cursor-not-allowed"
                                >
                                    Validasi Pembayaran
                                </button>
                            </div>
                        </div>

                        <!-- Method : Debit -->
                        <div id="methodDebitContent" class="hidden space-y-3">
                            <h3 class="text-[20px] leading-7 font-bold text-black">
                                Pembayaran Debit
                            </h3>
                            <p class="border border-amber-300 bg-amber-50 rounded-md px-2 py-2 text-xs text-neutral-800">Lanjutkan pembayaran di mesin EDC.</p>
                            <div class="pt-1">
                                <button type="button" id="btnValidateDebit" class="h-[35px] px-5 rounded-[10px] text-[12px] font-medium bg-black text-white hover:bg-neutral-800 transition cursor-pointer">
                                    Pembayaran Berhasil
                                </button>
                            </div>
                        </div>

                        <!-- Method : qris-->
                        <div id="methodQRISContent" class="hidden space-y-3">
                            <h3 class="text-[20px] leading-7 font-bold text-black">
                                Pembayaran QRIS
                            </h3>
                            <p class="border border-amber-300 bg-amber-50 rounded-md px-2 py-2 text-xs text-neutral-800">Lanjutkan pembayaran di mesin QRIS.</p>
                            <div class="pt-1">
                                <button type="button" id="btnValidateQRIS" class="h-[35px] px-5 rounded-[10px] text-[12px] font-medium bg-black text-white hover:bg-neutral-800 transition cursor-pointer">
                                    Pembayaran Berhasil
                                </button>
                            </div>
                        </div>

                    </div>
                </section>

                <!--RIGHT AREA: DETAIL ORDER-->
                <aside 
                    id="paymentOrderDetailsSidebar"
                    class="w-full lg:w-[383px] shrink-0 bg-white border-t lg:border-t-0 lg:border-l border-black/10 flex flex-col justify-between h-auto lg:h-screen lg:sticky lg:top-0 z-20 px-[25px] py-[35px]"
                >
                    <!-- Header with ✕ close button -->
                    <div class="flex items-center justify-between pb-[36px] border-b border-black/30">
                        <h2 class="text-[18px] leading-7 font-bold text-black">Order Details</h2>
                        <button 
                            type="button" 
                            id="btnClosePaymentDetail" 
                            class="text-neutral-400 hover:text-neutral-800 text-xl font-light leading-none p-1 transition cursor-pointer select-none"
                            title="Kembali ke Katalog"
                        >
                            &times;
                        </button>
                    </div>

                    <!-- Items list in Payment View -->
                    <div 
                        id="paymentCartList" 
                        class="flex-1 overflow-y-auto py-[10px] space-y-[10px] max-h-[50vh] lg:max-h-none"
                    >
                        <!-- Rendered via JavaScript -->
                    </div>

                    <!-- Summary & Ubah Order Button -->
                    <div class="border-t border-black/30 bg-white pt-5 space-y-[15px]">
                        <div class="space-y-[5px] text-base">
                            <div class="flex items-center justify-between">
                                <span class="text-[#0F172A] font-normal">Total Item</span>
                                <span id="paymentTotalItem" class="font-normal text-black">0 pcs</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#0F172A] font-normal">Subtotal</span>
                                <span id="paymentSubtotal" class="font-normal text-black">Rp0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[#0F172A] font-normal">Diskon</span>
                                <span id="paymentDiskon" class="font-normal text-black">Rp0</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between py-[15px] border-t border-black/30">
                            <span class="text-[18px] font-bold text-[#0F172A]">Total</span>
                            <span id="paymentTotal" class="text-[20px] font-bold text-[#0F172A]">Rp0</span>
                        </div>

                        <!-- Ubah Order Button (Kembali ke Katalog) -->
                        <button 
                            type="button" 
                            id="btnBackToOrder" 
                            class="w-full h-[59px] border border-black/30 hover:bg-neutral-50 active:scale-[0.98] text-black text-2xl font-medium rounded-[10px] transition duration-150 flex items-center justify-center cursor-pointer"
                        >
                            Ubah Order
                        </button>
                    </div>
                </aside>

            </div>

            <!--VIEW RECEIPT -->
            <div id="viewReceipt" class="hidden flex-1 flex flex-col lg:flex-row bg-white transition-all">
                
                <!-- LEFT/CENTER AREA: THERMAL RECEIPT & ACTION BUTTONS -->
                <section class="flex-1 min-w-0 p-[30px] overflow-y-auto max-h-screen bg-white">
                    <h1 class="text-[32px] font-bold text-black leading-[39px] mb-[30px]">
                        Detail Pesanan
                    </h1>

                    <!--Paper Thermal Struk Card-->
                    <div 
                        id="printableReceipt" 
                        class="bg-white rounded-2xl border border-black/15 p-[25px] w-[392px] max-w-full text-xs font-normal text-black"
                    >
                        <!-- Receipt Top Brand -->
                        <div class="text-center">
                            <div class="text-base font-bold text-black tracking-tight">KasirAja</div>
                            <div class="text-[10px] text-black/60 mt-0.5">Toko Sepatu · Struk Pembelian</div>
                        </div>

                        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

                        <!-- Meta Info: No Nota, Tanggal, Kasir -->
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-black/60">No. nota</span>
                                <span id="receiptNoNota" class="font-bold text-black">TRX-20261120-003</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60">Tanggal</span>
                                <span id="receiptTanggal" class="font-normal text-black">20 Nov 2026, 14:56</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60">Kasir</span>
                                <span id="receiptKasir" class="font-normal text-black">Kasir 1</span>
                            </div>
                        </div>

                        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

                        <!-- Purchased Items List -->
                        <div id="receiptItemsContainer" class="space-y-3.5">
                            <!-- Items injected dynamically via JS -->
                        </div>

                        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

                        <!-- Subtotal, Pajak, Diskon -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-black/60" id="receiptSubtotalLabel">Subtotal (70 pasang)</span>
                                <span id="receiptSubtotalVal" class="font-medium text-black">Rp466.033.500</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60">Diskon</span>
                                <span id="receiptDiskonVal" class="font-medium text-black">Rp0</span>
                            </div>
                        </div>

                        <div class="border-b border-dashed border-black/25 my-[15px]"></div>

                        <!-- Total Bayar, Metode, Jumlah Bayar, Kembalian -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between text-sm font-semibold text-black">
                                <span>Total bayar</span>
                                <span id="receiptTotalBayarVal">Rp466.033.500</span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-black/60">Metode</span>
                                <span id="receiptMetodeVal" class="font-medium text-black">Tunai</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60">Jumlah Bayar</span>
                                <span id="receiptJumlahBayarVal" class="font-medium text-black">Rp466.100.000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-black/60">Kembalian</span>
                                <span id="receiptKembalianVal" class="font-medium text-black">Rp66.500</span>
                            </div>
                        </div>

                        <!-- Selesai Pill Badge -->
                        <div class="my-5 text-center">
                            <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#E6F9F0] text-[#00A854] border border-[#B7F4D8]">
                                SELESAI
                            </span>
                        </div>

                        <!-- Footer Note -->
                        <div class="text-center text-[11px] text-neutral-400">
                            Terimakasih sudah berbelanja.
                        </div>
                    </div>

                    <!-- Bottom Buttons: Unduh Struk (PDF) & Salin Teks Struk -->
                    <div class="flex items-center gap-3 mt-6 max-w-md w-full">
                        <button 
                            type="button" 
                            id="btnPrintPdf" 
                            class="h-[54px] px-[25px] bg-black hover:bg-neutral-800 active:scale-[0.98] text-white text-[20px] font-medium rounded-[10px] transition duration-150 flex items-center justify-center cursor-pointer"
                        >
                            Unduh Struk (PDF)
                        </button>
                        <button 
                            type="button" 
                            id="btnCopyReceipt" 
                            class="h-[54px] px-[25px] border border-black/20 hover:bg-neutral-50 active:scale-[0.98] text-black text-[20px] font-medium rounded-[10px] transition duration-150 flex items-center justify-center cursor-pointer"
                        >
                            Salin Teks Struk
                        </button>
                    </div>
                </section>

                <!-- RIGHT AREA: RINKASAN TRANSAKSI (Sesuai Page Payment-1.png) -->
                <aside 
                    id="receiptSummarySidebar"
                    class="w-full lg:w-[410px] xl:w-[430px] shrink-0 bg-white border-t lg:border-t-0 lg:border-l border-neutral-200/90 p-6 sm:p-7 flex flex-col justify-between h-auto lg:h-screen lg:sticky lg:top-0 z-20"
                >
                    <div>
                        <!-- Header with Status Badge -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h2 class="text-base font-bold text-neutral-900 tracking-tight">Rinkasan Transaksi</h2>
                                <p id="sideReceiptNota" class="text-xs text-neutral-400 mt-0.5">TRX-20261120-003</p>
                            </div>
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#E6F9F0] text-[#00A854] border border-[#B7F4D8]">
                                SELESAI
                            </span>
                        </div>

                        <div class="border-b border-neutral-100 my-4"></div>

                        <!-- Info Card (Grey rounded container) -->
                        <div class="bg-neutral-50 rounded-xl p-4 text-xs space-y-2 text-neutral-600 mb-6 border border-neutral-100/80">
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Tanggal</span>
                                <span id="sideReceiptTanggal" class="font-medium text-neutral-900">20 Nov 2026, 14:56</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Kasir</span>
                                <span id="sideReceiptKasir" class="font-medium text-neutral-900">Kasir 1</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Metode</span>
                                <span id="sideReceiptMetode" class="font-medium text-neutral-900">Tunai</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Total Item</span>
                                <span id="sideReceiptTotalItem" class="font-medium text-neutral-900">70 pasang</span>
                            </div>
                        </div>

                        <!-- Subtotal & Diskon -->
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Subtotal</span>
                                <span id="sideReceiptSubtotal" class="font-medium text-neutral-900">Rp419.850.000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Diskon</span>
                                <span id="sideReceiptDiskon" class="font-medium text-neutral-900">Rp0</span>
                            </div>
                        </div>

                        <!-- Dashed divider -->
                        <div class="border-b border-dashed border-neutral-200 my-4"></div>

                        <!-- Total Bayar, Jumlah Bayar, Kembalian -->
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-neutral-900 tracking-tight">Total Bayar</span>
                                <span id="sideReceiptTotalBayar" class="text-xl font-black text-neutral-900 tracking-tight">Rp466.033.500</span>
                            </div>
                            <div class="flex justify-between text-xs text-neutral-600 mt-2">
                                <span>Jumlah Bayar</span>
                                <span id="sideReceiptJumlahBayar" class="font-medium">Rp466.100.000</span>
                            </div>
                            <div class="flex justify-between text-xs text-neutral-600 mt-1">
                                <span>Kembalian</span>
                                <span id="sideReceiptKembalian" class="font-medium text-emerald-600">Rp66.500</span>
                            </div>
                        </div>
                    </div>

                    <!-- Transaksi Baru Button -->
                    <div class="pt-6">
                        <button 
                            type="button" 
                            id="btnNewTransaction" 
                            class="w-full h-[59px] border border-black/30 hover:bg-neutral-50 active:scale-[0.98] text-black text-2xl font-medium rounded-[10px] transition duration-150 flex items-center justify-center cursor-pointer"
                        >
                            Transaksi Baru
                        </button>
                    </div>
                </aside>

            </div>

        </div>
    </div>

    <!-- POPUP PILIH UKURAN (gaya modal sama dengan modal CRUD di Figma) -->
    <div id="sizeModal" class="fixed inset-y-0 right-0 left-[var(--sidebar-width)] z-50 hidden items-center justify-center bg-black/45 p-4" role="dialog" aria-modal="true" aria-labelledby="sizeModalTitle">
        <div class="w-full max-w-[462px] bg-white rounded-2xl border border-[#E7E7E7]/90 p-5">
            <div class="flex items-center justify-between pb-[18px] border-b border-black/10">
                <h3 class="text-base font-semibold text-black">Pilih Ukuran</h3>
                <button type="button" id="sizeModalClose" class="w-[22px] h-[22px] rounded-[3px] border border-black/25 flex items-center justify-center text-neutral-500 hover:text-black cursor-pointer" aria-label="Tutup">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="flex items-center gap-[15px] pt-5">
                <div class="w-20 h-20 rounded-[10px] border border-black/20 bg-neutral-100 overflow-hidden shrink-0">
                    <img id="sizeModalImage" src="" alt="" class="w-full h-full object-cover">
                </div>
                <div class="min-w-0">
                    <p id="sizeModalTitle" class="text-base font-bold text-black leading-5">Nama Produk</p>
                    <p id="sizeModalPrice" class="text-sm font-semibold text-black mt-[2px] leading-5"></p>
                    <p class="text-[12px] text-black/60 mt-[2px]">Pilih ukuran yang dibeli pelanggan</p>
                </div>
            </div>
            <p class="text-[14px] font-semibold text-black mt-5 mb-[5px]">Ukuran <span class="text-red-500">*</span></p>
            <div id="sizeModalOptions" class="grid grid-cols-3 gap-[10px]"></div>
            <div class="flex items-center justify-between gap-[10px] pt-5 mt-5 border-t border-black/15">
                <div class="flex items-center h-[30px] border border-black/40 rounded-[7px] px-[5px] text-sm font-semibold text-black gap-[10px]">
                    <button type="button" id="sizeQtyMinus" class="px-1 cursor-pointer select-none">-</button>
                    <span id="sizeQtyVal" class="min-w-5 text-center select-none font-bold">1</span>
                    <button type="button" id="sizeQtyPlus" class="px-1 cursor-pointer select-none">+</button>
                </div>
                <button type="button" id="sizeModalAdd" disabled class="h-[30px] px-3 rounded-[5px] border border-black/30 text-[12px] font-semibold text-white bg-neutral-300 cursor-not-allowed transition">
                    Pilih ukuran dulu
                </button>
            </div>
        </div>
    </div>

    <!--JAVASCRIPT: POS CART, PAYMENT & RECEIPT CONTROLLER-->
    <script>
        // Currency formatter
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(Math.round(number));
        }

        // Keranjang dimulai kosong (dulu berisi 4 item demo produk lama yang sudah dihapus)
        const DEFAULT_CART_ITEMS = [];

        let cart = [];
        try {
            const saved = sessionStorage.getItem('kasir_cart_v3');
            if (saved) {
                cart = JSON.parse(saved);
            } else {
                cart = [...DEFAULT_CART_ITEMS];
            }
        } catch (e) {
            cart = [...DEFAULT_CART_ITEMS];
        }

        let selectedPaymentMethod = 'Tunai';
        let lastTransactionResult = null;

        // View Switcher Function (catalog, payment, receipt)
        function showView(viewName) {
            const vCatalog = document.getElementById('viewCatalog');
            const vPayment = document.getElementById('viewPayment');
            const vReceipt = document.getElementById('viewReceipt');

            if (vCatalog) vCatalog.classList.add('hidden');
            if (vPayment) vPayment.classList.add('hidden');
            if (vReceipt) vReceipt.classList.add('hidden');

            if (viewName === 'catalog' && vCatalog) {
                vCatalog.classList.remove('hidden');
            } else if (viewName === 'payment' && vPayment) {
                vPayment.classList.remove('hidden');
                setupPaymentView();
            } else if (viewName === 'receipt' && vReceipt) {
                vReceipt.classList.remove('hidden');
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function saveCart() {
            sessionStorage.setItem('kasir_cart_v3', JSON.stringify(cart));
            renderCart();
            if (!document.getElementById('viewPayment').classList.contains('hidden')) {
                setupPaymentView();
            }
        }

        // Tambah 1 pasang, tidak boleh melebihi stok varian
        function increaseQty(idx) {
            const item = cart[idx];
            if (!item) return;
            if (item.stock !== undefined && item.qty >= item.stock) {
                showFlash('Stok Tidak Cukup', `${item.name} ${item.variant} hanya tersisa ${item.stock} pasang.`, 'error');
                return;
            }
            item.qty += 1;
            saveCart();
        }

        function calculateCart() {
            let totalQty = 0;
            let subtotal = 0;

            cart.forEach(item => {
                totalQty += item.qty;
                subtotal += (item.price * item.qty);
            });

            // Tanpa PPN. Total akhir tetap dihitung ulang oleh server dari harga di database.
            const diskon = 0;
            const total = subtotal - diskon;

            return { totalQty, subtotal, diskon, total };
        }

// Render Cart items in Catalog Sidebar
        function renderCart() {
            const container = document.getElementById('cartList');
            if (!container) return;

            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="py-12 text-center text-neutral-400">
                        <svg class="w-10 h-10 mx-auto text-neutral-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <p class="text-xs font-medium">Belum ada pesanan</p>
                        <p class="text-[11px] text-neutral-400 mt-0.5">Pilih produk untuk ditambahkan</p>
                    </div>
                `;
            } else {
                let html = '';
                cart.forEach((item, index) => {
                    const bgBox = item.isBlack ? 'bg-black' : 'bg-neutral-100';
                    const varText = item.variant || '-';
                    html += `
                        <div class="flex items-center gap-[10px] py-[5px] group">
                            <div class="w-20 h-20 rounded-[10px] ${bgBox} flex items-center justify-center shrink-0 overflow-hidden">
                                <img src="${item.image}" alt="${item.name}" class="h-full w-full object-cover pointer-events-none" onerror="this.src='{{ asset("images/shoes/placeholder.svg") }}'">
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-bold text-black truncate leading-5">${item.name}</h4>
                                <p class="text-sm text-black font-normal leading-[17px]">${varText}</p>
                                <p class="text-sm font-semibold text-black mt-[10px] leading-[17px]">Rp${formatRupiah(item.price)}</p>
                            </div>

                            <div class="flex items-center gap-[10px] shrink-0">
                                <div class="flex items-center h-[27px] border border-black/40 rounded-[7px] px-[5px] text-sm font-semibold text-black gap-[10px]">
                                    <button type="button" class="btn-step-minus hover:text-neutral-600 select-none cursor-pointer" data-index="${index}">-</button>
                                    <span class="min-w-5 text-center select-none text-sm font-bold">${item.qty}</span>
                                    <button type="button" class="btn-step-plus hover:text-neutral-600 select-none cursor-pointer" data-index="${index}">+</button>
                                </div>
                                <button type="button" class="btn-remove-item text-neutral-400 hover:text-red-500 transition cursor-pointer p-0.5" data-index="${index}" title="Hapus">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                });
                container.innerHTML = html;
            }

            const { totalQty, subtotal, diskon, total } = calculateCart();
            document.getElementById('cartTotalItem').textContent = `${totalQty} pcs`;
            document.getElementById('cartSubtotal').textContent = 'Rp' + formatRupiah(subtotal);
            document.getElementById('cartDiskon').textContent = 'Rp' + formatRupiah(diskon);
            document.getElementById('cartTotal').textContent = 'Rp' + formatRupiah(total);

            // Stepper & Trash Listeners
            container.querySelectorAll('.btn-step-minus').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const idx = parseInt(e.currentTarget.getAttribute('data-index'));
                    if (cart[idx].qty > 1) {
                        cart[idx].qty -= 1;
                    } else {
                        cart.splice(idx, 1);
                    }
                    saveCart();
                });
            });

            container.querySelectorAll('.btn-step-plus').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    increaseQty(parseInt(e.currentTarget.getAttribute('data-index')));
                });
            });

            container.querySelectorAll('.btn-remove-item').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const idx = parseInt(e.currentTarget.getAttribute('data-index'));
                    cart.splice(idx, 1);
                    saveCart();
                });
            });
        }

        // Setup Layar Pembayaran (Page Payment.png)
        function setupPaymentView() {
            const paymentCartList = document.getElementById('paymentCartList');
            const { totalQty, subtotal, diskon, total } = calculateCart();

            // 1. Render items list in right sidebar
            if (paymentCartList) {
                if (cart.length === 0) {
                    paymentCartList.innerHTML = `<div class="py-8 text-center text-neutral-400 text-xs">Pesanan kosong.</div>`;
                } else {
                    let html = '';
                    cart.forEach((item, index) => {
                        const bgBox = item.isBlack ? 'bg-black' : 'bg-neutral-100';
                        const varText = item.variant || '-';
                        html += `
                            <div class="flex items-center gap-[10px] py-[5px] group">
                                <div class="w-20 h-20 rounded-[10px] ${bgBox} flex items-center justify-center shrink-0 overflow-hidden">
                                    <img src="${item.image}" alt="${item.name}" class="h-full w-full object-cover pointer-events-none" onerror="this.src='{{ asset("images/shoes/placeholder.svg") }}'">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-bold text-black truncate leading-5">${item.name}</h4>
                                    <p class="text-sm text-black font-normal leading-[17px]">${varText}</p>
                                    <p class="text-sm font-semibold text-black mt-[10px] leading-[17px]">Rp${formatRupiah(item.price)}</p>
                                </div>

                                <div class="flex items-center gap-[10px] shrink-0">
                                    <div class="flex items-center h-[27px] border border-black/40 rounded-[7px] px-[5px] text-sm font-semibold text-black gap-[10px]">
                                        <button type="button" class="btn-step-minus-pay hover:text-neutral-600 select-none cursor-pointer" data-index="${index}">-</button>
                                        <span class="min-w-5 text-center select-none text-sm font-bold">${item.qty}</span>
                                        <button type="button" class="btn-step-plus-pay hover:text-neutral-600 select-none cursor-pointer" data-index="${index}">+</button>
                                    </div>
                                    <button type="button" class="btn-remove-item-pay text-neutral-400 hover:text-red-500 transition cursor-pointer p-0.5" data-index="${index}" title="Hapus">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    paymentCartList.innerHTML = html;

                    paymentCartList.querySelectorAll('.btn-step-minus-pay').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const idx = parseInt(e.currentTarget.getAttribute('data-index'));
                            if (cart[idx].qty > 1) {
                                cart[idx].qty -= 1;
                            } else {
                                cart.splice(idx, 1);
                            }
                            saveCart();
                        });
                    });

                    paymentCartList.querySelectorAll('.btn-step-plus-pay').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            increaseQty(parseInt(e.currentTarget.getAttribute('data-index')));
                        });
                    });

                    paymentCartList.querySelectorAll('.btn-remove-item-pay').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const idx = parseInt(e.currentTarget.getAttribute('data-index'));
                            cart.splice(idx, 1);
                            saveCart();
                        });
                    });
                }
            }

            // Update totals in right sidebar
            document.getElementById('paymentTotalItem').textContent = `${totalQty} pcs`;
            document.getElementById('paymentSubtotal').textContent = 'Rp' + formatRupiah(subtotal);
            document.getElementById('paymentDiskon').textContent = 'Rp' + formatRupiah(diskon);
            document.getElementById('paymentTotal').textContent = 'Rp' + formatRupiah(total);

            // Generate suggested quick amounts
            const quickWrapper = document.getElementById('quickAmountWrapper');
            if (quickWrapper) {
                // Round up suggestions based on total
                const ceil50k = Math.ceil(total / 50000) * 50000;
                const opt1 = (ceil50k === total) ? ceil50k + 50000 : ceil50k;
                const opt2 = opt1 + 50000;
                const opt3 = Math.ceil(total / 50000000) * 50000000 || 500000000;

                quickWrapper.innerHTML = `
                    <button type="button" id="btnUangPasQuick" class="h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer">
                        Uang pas
                    </button>
                    <button type="button" class="btn-quick-pill h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer" data-val="${opt1}">
                        Rp${formatRupiah(opt1)}
                    </button>
                    <button type="button" class="btn-quick-pill h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer" data-val="${opt2}">
                        Rp${formatRupiah(opt2)}
                    </button>
                    <button type="button" class="btn-quick-pill h-[25px] px-[10px] rounded-2xl text-[12px] font-normal border border-[#E0E0E0] bg-white hover:bg-neutral-100 text-black transition cursor-pointer" data-val="${opt3}">
                        Rp${formatRupiah(opt3)}
                    </button>
                `;

                document.getElementById('btnUangPasQuick')?.addEventListener('click', () => {
                    const inp = document.getElementById('inputPaymentReceived');
                    if (inp) {
                        inp.value = 'Rp ' + formatRupiah(total);
                        calculatePaymentChange();
                    }
                });

                quickWrapper.querySelectorAll('.btn-quick-pill').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const val = parseFloat(btn.getAttribute('data-val'));
                        const inp = document.getElementById('inputPaymentReceived');
                        if (inp) {
                            inp.value = 'Rp ' + formatRupiah(val);
                            calculatePaymentChange();
                        }
                    });
                });
            }

            calculatePaymentChange();
        }

        // Calculate Kembalian & Toggle Validasi Pembayaran button
        function calculatePaymentChange() {
            const { total } = calculateCart();
            const inputVal = document.getElementById('inputPaymentReceived')?.value || '';
            const rawNumbers = inputVal.replace(/[^0-9]/g, '');
            const bayar = parseFloat(rawNumbers) || 0;

            const kembalian = Math.max(0, bayar - total);
            const kembalianText = document.getElementById('paymentKembalianText');
            if (kembalianText) {
                kembalianText.textContent = 'Rp' + formatRupiah(kembalian);
            }

            const btnValidate = document.getElementById('btnValidatePayment');
            if (btnValidate) {
                if (bayar >= total && total > 0) {
                    btnValidate.className = 'h-[35px] px-5 rounded-[10px] text-[12px] font-medium transition duration-150 bg-black text-white hover:bg-neutral-800 active:scale-[0.98] cursor-pointer';
                } else {
                    btnValidate.className = 'h-[35px] px-5 rounded-[10px] text-[12px] font-medium transition duration-150 bg-neutral-300 text-white cursor-not-allowed';
                }
            }
        }

        // Format raw input as typing
        const paymentInput = document.getElementById('inputPaymentReceived');
        if (paymentInput) {
            paymentInput.addEventListener('input', (e) => {
                const raw = e.target.value.replace(/[^0-9]/g, '');
                if (raw) {
                    e.target.value = 'Rp ' + formatRupiah(parseFloat(raw));
                } else {
                    e.target.value = '';
                }
                calculatePaymentChange();
            });
        }

        // Payment Method Card Selection (Tunai, Debit, QRIS)
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.payment-method-btn').forEach(b => {
                    b.classList.remove('border-2', 'border-neutral-900', 'active-method');
                    b.classList.add('border', 'border-neutral-200');
                });
                btn.classList.add('border-2', 'border-neutral-900', 'active-method');
                btn.classList.remove('border-neutral-200');
                selectedPaymentMethod = btn.getAttribute('data-method');

                const tunaiCont = document.getElementById('methodTunaiContent');
                const debitCont = document.getElementById('methodDebitContent');
                const qrisCont = document.getElementById('methodQRISContent');

                if (tunaiCont) tunaiCont.classList.add('hidden');
                if (debitCont) debitCont.classList.add('hidden');
                if (qrisCont) qrisCont.classList.add('hidden');

                if (selectedPaymentMethod === 'Tunai' && tunaiCont) tunaiCont.classList.remove('hidden');
                if (selectedPaymentMethod === 'Debit' && debitCont) debitCont.classList.remove('hidden');
                if (selectedPaymentMethod === 'QRIS' && qrisCont) qrisCont.classList.remove('hidden');
            });
        });

        // Continue Button (from Catalog to Layar Pembayaran)
        const btnContinueOrder = document.getElementById('btnContinueOrder');
        if (btnContinueOrder) {
            btnContinueOrder.addEventListener('click', () => {
                if (cart.length === 0) {
                    showFlash('Pesanan Kosong', 'Silakan pilih produk terlebih dahulu sebelum melanjutkan.', 'error');
                    return;
                }
                showView('payment');
            });
        }

        // Ubah Order & Close buttons (from Payment back to Catalog)
        document.getElementById('btnBackToOrder')?.addEventListener('click', () => showView('catalog'));
        document.getElementById('btnClosePaymentDetail')?.addEventListener('click', () => showView('catalog'));

        // Process Transaction Function
        // Server menghitung ulang harga, cek stok, dan menolak jika ada masalah (HTTP 422).
        // Struk hanya ditampilkan kalau server benar-benar menyimpan transaksi.
        let checkoutInFlight = false;
        async function processCheckout() {
            if (checkoutInFlight) return;
            const { total, diskon } = calculateCart();
            const inputVal = document.getElementById('inputPaymentReceived')?.value || '';
            const rawNumbers = inputVal.replace(/[^0-9]/g, '');
            let bayar = parseFloat(rawNumbers);

            if (selectedPaymentMethod !== 'Tunai' || isNaN(bayar)) {
                bayar = total;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const buttons = ['btnValidatePayment', 'btnValidateDebit', 'btnValidateQRIS']
                .map(id => document.getElementById(id)).filter(Boolean);
            const oldLabels = buttons.map(b => b.textContent);
            checkoutInFlight = true;
            buttons.forEach(b => { b.disabled = true; b.textContent = 'Memproses...'; });

            try {
                const payload = {
                    items: cart.map(item => ({ id_varian: item.variantId, jumlah: item.qty })),
                    metode_pembayaran: selectedPaymentMethod,
                    jumlah_bayar: bayar,
                    diskon: diskon
                };

                const response = await fetch("{{ route('kasir.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                let result = {};
                try { result = await response.json(); } catch (e) { /* bukan JSON */ }

                if (!response.ok || !result.success) {
                    let msg = result.message || 'Transaksi gagal diproses. Silakan coba lagi.';
                    if (result.errors) {
                        const first = Object.values(result.errors)[0];
                        if (first && first[0]) msg = first[0];
                    }
                    if (response.status === 419) msg = 'Sesi login habis. Muat ulang halaman lalu coba lagi.';
                    showFlash('Transaksi Gagal', msg, 'error');
                    return;
                }

                lastTransactionResult = result;
                renderReceipt(result);
                showView('receipt');
                showFlash('Transaksi Berhasil!', `No. Nota: ${result.no_nota}. Pembayaran telah tervalidasi.`);
            } catch (err) {
                console.warn('Checkout gagal:', err);
                showFlash('Koneksi Gagal', 'Tidak dapat terhubung ke server. Transaksi BELUM tersimpan, silakan coba lagi.', 'error');
            } finally {
                checkoutInFlight = false;
                buttons.forEach((b, k) => { b.disabled = false; b.textContent = oldLabels[k]; });
            }
        }

        // Attach listener to Validasi buttons
        document.getElementById('btnValidatePayment')?.addEventListener('click', () => {
            const { total } = calculateCart();
            const inputVal = document.getElementById('inputPaymentReceived')?.value || '';
            const rawNumbers = inputVal.replace(/[^0-9]/g, '');
            const bayar = parseFloat(rawNumbers) || 0;

            if (bayar < total) {
                showFlash('Nominal Kurang', 'Jumlah uang diterima belum mencukupi total tagihan.', 'error');
                return;
            }
            processCheckout();
        });

        document.getElementById('btnValidateDebit')?.addEventListener('click', processCheckout);
        document.getElementById('btnValidateQRIS')?.addEventListener('click', processCheckout);

        // Render Struk & Rinkasan Transaksi (Page Payment-1.png)
        function renderReceipt(data) {
            const totalQty = data.total_item ?? 0;
            const subtotal = data.subtotal ?? 0;
            const diskon = data.diskon ?? 0;
            const total = data.total_bayar ?? 0;
            const nota = data.no_nota || '-';
            const tgl = data.tanggal_transaksi || '-';
            const kasir = data.kasir_nama || '-';
            const bayar = data.jumlah_bayar ?? total;
            const kembalian = data.kembalian ?? 0;
            const metode = data.metode_pembayaran || '-';

            // Struk Elements
            document.getElementById('receiptNoNota').textContent = nota;
            document.getElementById('receiptTanggal').textContent = tgl;
            document.getElementById('receiptKasir').textContent = kasir;
            document.getElementById('receiptSubtotalLabel').textContent = `Subtotal (${totalQty} pasang)`;
            document.getElementById('receiptSubtotalVal').textContent = 'Rp' + formatRupiah(subtotal);
            document.getElementById('receiptDiskonVal').textContent = 'Rp' + formatRupiah(diskon);
            document.getElementById('receiptTotalBayarVal').textContent = 'Rp' + formatRupiah(total);
            document.getElementById('receiptMetodeVal').textContent = metode;
            document.getElementById('receiptJumlahBayarVal').textContent = 'Rp' + formatRupiah(bayar);
            document.getElementById('receiptKembalianVal').textContent = 'Rp' + formatRupiah(kembalian);

            // Struk Item lines
            const itemsCont = document.getElementById('receiptItemsContainer');
            if (itemsCont) {
                let html = '';
                (data.items || []).forEach(item => {
                    const varStr = `Ukuran ${item.ukuran} EUR · ${item.warna} · ${item.sku}`;
                    html += `
                        <div>
                            <div class="font-bold text-xs text-neutral-900">${item.nama}</div>
                            <div class="text-[11px] text-neutral-400 mt-0.5 leading-snug">${varStr}</div>
                            <div class="flex justify-between text-xs font-normal text-neutral-800 mt-1">
                                <span>${item.jumlah} x Rp${formatRupiah(item.harga)}</span>
                                <span class="font-medium text-neutral-900">Rp${formatRupiah(item.subtotal)}</span>
                            </div>
                        </div>
                    `;
                });
                itemsCont.innerHTML = html;
            }

            // Right Sidebar (Rinkasan Transaksi)
            document.getElementById('sideReceiptNota').textContent = nota;
            document.getElementById('sideReceiptTanggal').textContent = tgl;
            document.getElementById('sideReceiptKasir').textContent = kasir;
            document.getElementById('sideReceiptMetode').textContent = metode;
            document.getElementById('sideReceiptTotalItem').textContent = `${totalQty} pasang`;
            document.getElementById('sideReceiptSubtotal').textContent = 'Rp' + formatRupiah(subtotal);
            document.getElementById('sideReceiptDiskon').textContent = 'Rp' + formatRupiah(diskon);
            document.getElementById('sideReceiptTotalBayar').textContent = 'Rp' + formatRupiah(total);
            document.getElementById('sideReceiptJumlahBayar').textContent = 'Rp' + formatRupiah(bayar);
            document.getElementById('sideReceiptKembalian').textContent = 'Rp' + formatRupiah(kembalian);
        }

        // Print PDF Button
        document.getElementById('btnPrintPdf')?.addEventListener('click', () => {
            window.print();
        });

        // Copy Text Receipt
        document.getElementById('btnCopyReceipt')?.addEventListener('click', () => {
            const data = lastTransactionResult;
            if (!data) return;
            const totalQty = data.total_item, subtotal = data.subtotal, diskon = data.diskon, total = data.total_bayar;
            const nota = data.no_nota, tgl = data.tanggal_transaksi, kasir = data.kasir_nama;
            const bayar = data.jumlah_bayar, kembalian = data.kembalian, metode = data.metode_pembayaran;

            let text = `=================================\n`;
            text += `             KasirAja\n`;
            text += `   Toko Sepatu · Struk Pembelian\n`;
            text += `=================================\n`;
            text += `No. nota : ${nota}\n`;
            text += `Tanggal  : ${tgl}\n`;
            text += `Kasir    : ${kasir}\n`;
            text += `---------------------------------\n`;
            (data.items || []).forEach(item => {
                text += `${item.nama}\n`;
                text += `Ukuran ${item.ukuran} · ${item.warna}\n`;
                text += `${item.jumlah} x Rp${formatRupiah(item.harga)} = Rp${formatRupiah(item.subtotal)}\n\n`;
            });
            text += `---------------------------------\n`;
            text += `Subtotal (${totalQty} pasang) : Rp${formatRupiah(subtotal)}\n`;
            text += `Diskon               : Rp${formatRupiah(diskon)}\n`;
            text += `---------------------------------\n`;
            text += `Total bayar          : Rp${formatRupiah(total)}\n`;
            text += `Metode               : ${metode}\n`;
            text += `Jumlah Bayar         : Rp${formatRupiah(bayar)}\n`;
            text += `Kembalian            : Rp${formatRupiah(kembalian)}\n`;
            text += `=================================\n`;
            text += `             SELESAI\n`;
            text += `    Terimakasih sudah berbelanja.\n`;
            text += `=================================`;

            navigator.clipboard.writeText(text).then(() => {
                showFlash('Teks Struk Disalin', 'Rincian struk pembelian berhasil disalin ke clipboard.');
            }).catch(() => {
                showFlash('Berhasil', 'Teks struk siap digunakan.');
            });
        });

        // Transaksi Baru Button
        document.getElementById('btnNewTransaction')?.addEventListener('click', () => {
            cart = [];
            sessionStorage.removeItem('kasir_cart_v3');
            // Muat ulang supaya angka stok di kartu produk ikut ter-update
            window.location.reload();
        });

        // Search & Filter in Catalog View
        const searchInput = document.getElementById('productSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', applyProductFilter);
        }

        const filterMenuBtn = document.getElementById('filterMenuBtn');
        const filterDropdown = document.getElementById('filterDropdown');
        const filterChevron = document.getElementById('filterChevron');
        const currentFilterLabel = document.getElementById('currentFilterLabel');
        let selectedCategoryId = '';

        if (filterMenuBtn && filterDropdown) {
            filterMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                filterDropdown.classList.toggle('hidden');
                if (filterChevron) {
                    filterChevron.classList.toggle('rotate-180');
                }
            });

            document.querySelectorAll('.category-filter-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    const catId = item.getAttribute('data-category-id');
                    const catName = item.getAttribute('data-category-name');
                    selectedCategoryId = catId;
                    if (currentFilterLabel) {
                        currentFilterLabel.textContent = `Filter to "${catName}"`;
                    }
                    filterDropdown.classList.add('hidden');
                    if (filterChevron) filterChevron.classList.remove('rotate-180');
                    applyProductFilter();
                });
            });

            document.addEventListener('click', (e) => {
                if (!filterMenuBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
                    filterDropdown.classList.add('hidden');
                    if (filterChevron) filterChevron.classList.remove('rotate-180');
                }
            });
        }

        function applyProductFilter() {
            const q = searchInput?.value.toLowerCase().trim() || '';
            const cards = document.querySelectorAll('.product-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const name = card.getAttribute('data-product-name')?.toLowerCase() || '';
                const matchName = !q || name.includes(q);
                let matchCat = true;

                if (selectedCategoryId) {
                    matchCat = (card.getAttribute('data-category-id') === selectedCategoryId);
                }

                if (matchName && matchCat) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const notice = document.getElementById('noResultsNotice');
            if (notice) {
                if (visibleCount === 0) notice.classList.remove('hidden');
                else notice.classList.add('hidden');
            }
        }

        // ===== Popup pilih ukuran =====
        const sizeModal = document.getElementById('sizeModal');
        let modalProduct = null;   // data produk yang sedang dipilih
        let modalVariant = null;   // varian (ukuran) yang dipilih
        let modalQty = 1;

        function qtyInCart(variantId) {
            const it = cart.find(c => c.variantId == variantId);
            return it ? it.qty : 0;
        }

        function refreshModalButton() {
            const addBtn = document.getElementById('sizeModalAdd');
            document.getElementById('sizeQtyVal').textContent = modalQty;
            if (modalVariant) {
                addBtn.disabled = false;
                addBtn.className = 'h-[30px] px-3 rounded-[5px] border border-black/30 text-[12px] font-semibold text-white bg-black hover:bg-neutral-800 cursor-pointer transition';
                addBtn.textContent = `Tambah · Rp${formatRupiah(modalProduct.price * modalQty)}`;
            } else {
                addBtn.disabled = true;
                addBtn.className = 'h-[30px] px-3 rounded-[5px] border border-black/30 text-[12px] font-semibold text-white bg-neutral-300 cursor-not-allowed transition';
                addBtn.textContent = 'Pilih ukuran dulu';
            }
        }

        function openSizeModal(btn) {
            let variants = [];
            try { variants = JSON.parse(btn.getAttribute('data-variants') || '[]'); } catch (e) {}
            modalProduct = {
                id: btn.getAttribute('data-product-id'),
                name: btn.getAttribute('data-product-name'),
                price: parseFloat(btn.getAttribute('data-product-price')),
                image: btn.getAttribute('data-product-image'),
                isBlack: btn.getAttribute('data-is-black') === '1',
                variants
            };
            modalVariant = null;
            modalQty = 1;

            document.getElementById('sizeModalTitle').textContent = modalProduct.name;
            document.getElementById('sizeModalPrice').textContent = 'Rp' + formatRupiah(modalProduct.price);
            const img = document.getElementById('sizeModalImage');
            img.src = modalProduct.image;
            img.alt = modalProduct.name;

            const wrap = document.getElementById('sizeModalOptions');
            if (variants.length === 0) {
                wrap.innerHTML = '<p class="col-span-3 text-xs text-neutral-500 py-4 text-center">Produk ini belum punya varian ukuran.</p>';
            } else {
                wrap.innerHTML = variants.map(v => {
                    const sisa = v.stok - qtyInCart(v.id);
                    const habis = sisa <= 0;
                    return `<button type="button" class="size-opt h-[58px] rounded-[10px] border text-center transition ${habis ? 'border-black/10 bg-neutral-50 text-black/25 cursor-not-allowed' : 'border-black/30 hover:border-black cursor-pointer'}"
                                data-id="${v.id}" ${habis ? 'disabled' : ''}>
                                <span class="block text-[15px] font-bold leading-[18px]">${v.ukuran}</span>
                                <span class="block text-[11px] leading-[14px]">${v.warna}</span>
                                <span class="block text-[11px] leading-[14px] ${habis ? '' : 'text-black/60'}">${habis ? 'Habis' : 'Stok ' + sisa}</span>
                            </button>`;
                }).join('');
                wrap.querySelectorAll('.size-opt:not([disabled])').forEach(opt => {
                    opt.addEventListener('click', () => {
                        wrap.querySelectorAll('.size-opt').forEach(o => o.classList.remove('!border-black', 'border-2', 'bg-neutral-100'));
                        opt.classList.add('!border-black', 'border-2', 'bg-neutral-100');
                        modalVariant = modalProduct.variants.find(v => v.id == opt.getAttribute('data-id'));
                        modalQty = 1;
                        refreshModalButton();
                    });
                });
            }
            refreshModalButton();
            sizeModal.classList.remove('hidden');
            sizeModal.classList.add('flex');
        }

        function closeSizeModal() {
            sizeModal.classList.add('hidden');
            sizeModal.classList.remove('flex');
        }

        document.getElementById('sizeModalClose')?.addEventListener('click', closeSizeModal);
        sizeModal?.addEventListener('click', (e) => { if (e.target === sizeModal) closeSizeModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !sizeModal.classList.contains('hidden')) closeSizeModal(); });

        document.getElementById('sizeQtyMinus')?.addEventListener('click', () => {
            if (modalQty > 1) { modalQty--; refreshModalButton(); }
        });
        document.getElementById('sizeQtyPlus')?.addEventListener('click', () => {
            if (!modalVariant) return;
            const sisa = modalVariant.stok - qtyInCart(modalVariant.id);
            if (modalQty >= sisa) {
                showFlash('Stok Tidak Cukup', `Ukuran ${modalVariant.ukuran} hanya tersisa ${sisa} pasang.`, 'error');
                return;
            }
            modalQty++;
            refreshModalButton();
        });

        document.getElementById('sizeModalAdd')?.addEventListener('click', () => {
            if (!modalVariant) return;
            const v = modalVariant, p = modalProduct;
            const existing = cart.find(c => c.variantId == v.id);
            if (existing) {
                existing.qty = Math.min(existing.qty + modalQty, v.stok);
                existing.stock = v.stok;
            } else {
                cart.push({
                    id: p.id,
                    variantId: v.id,
                    name: p.name,
                    price: p.price,
                    qty: Math.min(modalQty, v.stok),
                    stock: v.stok,
                    image: p.image,
                    isBlack: p.isBlack,
                    variant: `Ukuran ${v.ukuran} · ${v.warna}`,
                    code: v.sku
                });
            }
            saveCart();
            closeSizeModal();
            showFlash('Ditambahkan', `${p.name} ukuran ${v.ukuran} (${modalQty} pasang) masuk ke pesanan.`);
        });

        // Add to Order buttons in catalog -> buka popup ukuran
        document.querySelectorAll('.btn-add-to-order').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.disabled) return;
                openSizeModal(btn);
            });
        });

        // Close / Clear order button (&times;) in catalog
        document.getElementById('btnCloseOrder')?.addEventListener('click', () => {
            if (cart.length === 0) return;
            if (confirm('Apakah Anda yakin ingin mengosongkan seluruh pesanan?')) {
                cart = [];
                saveCart();
            }
        });

        // Notifikasi memakai flash alert standar (layouts/flash-alert.blade.php)

        // Initialize on Load
        document.addEventListener('DOMContentLoaded', () => {
            renderCart();
            applyProductFilter();
        });
    </script>
</body>
</html>
