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

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
                            sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
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
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
                <section class="flex-1 min-w-0 p-5 sm:p-7 lg:p-8 overflow-y-auto max-h-screen bg-white">
                    
                    <!-- TOP BAR: SEARCH & FILTER -->
                    <div class="flex items-center gap-3 sm:gap-4 mb-7">
                        <!-- Search Input -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                                <svg class="w-4 h-4 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="productSearchInput" 
                                placeholder="Search" 
                                class="w-full pl-10 pr-4 py-2 bg-white border border-neutral-200 rounded-lg text-sm text-neutral-800 placeholder-neutral-400 focus:outline-none focus:border-neutral-400 focus:ring-1 focus:ring-neutral-400 transition"
                            >
                        </div>

                        <!-- Filter Dropdown Button -->
                        <div class="relative">
                            <button 
                                type="button" 
                                id="filterMenuBtn" 
                                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-neutral-200 rounded-lg text-sm font-normal text-neutral-700 hover:bg-neutral-50 transition cursor-pointer whitespace-nowrap"
                            >
                                <span id="currentFilterLabel">Filter to "Semua Kategori"</span>
                                <svg class="w-4 h-4 text-neutral-500 transition-transform duration-200" id="filterChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    <div id="productGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        @forelse ($displayProduks as $p)
                            @php
                                $isFayer = str_contains(strtolower($p->nama_produk), 'fayer');
                            @endphp
                            <div 
                                class="product-card bg-white rounded-2xl border border-neutral-200/90 p-4 shadow-[0_1px_3px_rgba(0,0,0,0.03)] hover:shadow-md transition-shadow duration-200 flex flex-col justify-between"
                                data-product-id="{{ $p->id_produk }}"
                                data-product-name="{{ $p->nama_produk }}"
                                data-category-id="{{ $p->id_kategori }}"
                            >
                                <div>
                                    <!-- Thumbnail image -->
                                    <div class="w-full h-44 rounded-xl flex items-center justify-center {{ $p->gambar_is_remote ? '' : 'p-3' }} relative overflow-hidden {{ $isFayer ? 'bg-black' : 'bg-neutral-100' }}">
                                        <img 
                                            src="{{ $p->gambar_url }}" 
                                            alt="{{ $p->nama_produk }}" 
                                            class="{{ $p->gambar_is_remote ? 'w-full h-full object-cover' : 'max-h-full max-w-full object-contain drop-shadow-sm' }} pointer-events-none"
                                            loading="lazy"
                                        >
                                    </div>

                                    <!-- Product Info -->
                                    <div class="mt-4">
                                        <h3 class="text-sm font-bold text-neutral-900 tracking-tight leading-snug">
                                            {{ $p->nama_produk }}
                                        </h3>
                                        <p class="text-xs font-semibold text-neutral-900 mt-1">
                                            Rp{{ number_format($p->harga_jual, 0, ',', '.') }}
                                        </p>
                                    </div>

                                    <!-- Product Meta: Warna, Ukuran, Stok -->
                                    <div class="mt-3 text-[11px] text-neutral-500">
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Merek</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-neutral-700 font-normal truncate">{{ $p->merek ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Warna</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-neutral-700 font-normal truncate">{{ $p->warna_text ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Ukuran</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-neutral-700 font-normal truncate">{{ $p->ukuran_text ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center">
                                            <span class="w-14 font-normal">Stok</span>
                                            <span class="mr-1.5">:</span>
                                            <span class="text-neutral-700 font-normal">{{ $p->total_stok ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add to Order Button -->
                                <div class="mt-4 pt-1">
                                    <button 
                                        type="button" 
                                        class="btn-add-to-order w-full py-2.5 {{ $p->total_stok > 0 ? 'bg-black hover:bg-neutral-800 active:scale-[0.98] cursor-pointer' : 'bg-neutral-300 cursor-not-allowed' }} text-white text-xs font-semibold rounded-lg transition duration-150 shadow-xs flex items-center justify-center"
                                        data-product-id="{{ $p->id_produk }}"
                                        data-product-name="{{ $p->nama_produk }}"
                                        data-product-price="{{ $p->harga_jual }}"
                                        data-product-image="{{ $p->gambar_url }}"
                                        data-is-black="{{ $isFayer ? '1' : '0' }}"
                                        data-variants='@json($p->varian_json)'
                                        {{ $p->total_stok > 0 ? '' : 'disabled' }}
                                    >
                                        {{ $p->total_stok > 0 ? 'Add to order' : 'Stok habis' }}
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
                    class="w-full lg:w-[390px] xl:w-[410px] shrink-0 bg-white border-t lg:border-t-0 lg:border-l border-neutral-200/90 flex flex-col justify-between h-auto lg:h-screen lg:sticky lg:top-0 z-20"
                    aria-label="Order details"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-neutral-100/90">
                        <h2 class="text-base font-bold text-neutral-900 tracking-tight">Order details</h2>
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
                        class="flex-1 overflow-y-auto px-6 py-4 space-y-4 max-h-[50vh] lg:max-h-none"
                    >
                        <!-- Items rendered via JavaScript -->
                    </div>

                    <!-- Summary & Checkout Section -->
                    <div class="p-6 border-t border-neutral-100 bg-white space-y-2.5">
                        <!-- Summary & Checkout Section -->
                        <div class="p-6 border-t border-neutral-100 bg-white space-y-2.5">
                        <!-- Total Item -->
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-500 font-normal">Total Item</span>
                            <span id="cartTotalItem" class="font-normal text-neutral-900">0 pcs</span>
                        </div>


                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-500 font-normal">Subtotal</span>
                            <span id="cartSubtotal" class="font-normal text-neutral-900">Rp0</span>
                        </div>


                        <!-- Diskon -->
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-500 font-normal">Diskon</span>
                            <span id="cartDiskon" class="font-normal text-neutral-900">Rp0</span>
                        </div>

                        <!-- Dashed divider line -->
                        <div class="border-b border-dashed border-neutral-200 my-2"></div>

                        <!-- Total -->
                        <div class="flex items-center justify-between pt-0.5">
                            <span class="text-base font-bold text-neutral-900 tracking-tight">Total</span>
                            <span id="cartTotal" class="text-lg font-bold text-neutral-900 tracking-tight">Rp0</span>
                        </div>

                        <!--Continue Button-->
                        <button 
                            type="button" 
                            id="btnContinueOrder" 
                            class="w-full mt-3 py-3.5 bg-black hover:bg-neutral-800 active:scale-[0.98] text-white text-sm font-semibold rounded-xl transition duration-150 shadow-sm flex items-center justify-center cursor-pointer"
                        >
                            Continue
                        </button>
                    </div>
                </aside>

            </div>

            <!--PAYMENT-->
            <div id="viewPayment" class="hidden flex-1 flex flex-col lg:flex-row bg-white transition-all">
                
                <!-- LEFT/CENTER AREA: METODE PEMBAYARAN -->
                <section class="flex-1 min-w-0 p-5 sm:p-7 lg:p-8 overflow-y-auto max-h-screen bg-white">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 tracking-tight leading-none mb-7">
                        Layar Pembayaran
                    </h1>

                    <!-- Payment Card Container -->
                    <div class="bg-white rounded-2xl border border-neutral-200/90 p-6 sm:p-8 shadow-xs">
                        
                        <!-- Header Section -->
                        <h2 class="text-sm sm:text-base font-bold text-neutral-900 mb-4 tracking-tight">
                            Pilih Metode Pembayaran
                        </h2>

                        <!--Payment Method Buttons tunai, debit, qris-->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-6">
                            <!--Tunai-->
                            <button 
                                type="button" 
                                class="payment-method-btn border-2 border-neutral-900 bg-white rounded-xl p-4 flex items-center gap-3 transition cursor-pointer text-left active-method"
                                data-method="Tunai"
                            >
                                <div class="w-6 h-6 flex items-center justify-center text-neutral-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                        <path d="M6 12h.01M18 12h.01"/>
                                    </svg>
                                </div>
                                <span class="font-bold text-sm text-neutral-900">Tunai</span>
                            </button>

                            <!--Debit-->
                            <button 
                                type="button" 
                                class="payment-method-btn border border-neutral-200 bg-white rounded-xl p-4 flex items-center gap-3 transition cursor-pointer text-left hover:border-neutral-400"
                                data-method="Debit"
                            >
                                <div class="w-6 h-6 flex items-center justify-center text-neutral-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="2" y="5" width="20" height="14" rx="2"/>
                                        <line x1="2" y1="10" x2="22" y2="10"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-sm text-neutral-800">Debit</span>
                            </button>

                            <!--qris-->
                            <button 
                                type="button" 
                                class="payment-method-btn border border-neutral-200 bg-white rounded-xl p-4 flex items-center gap-3 transition cursor-pointer text-left hover:border-neutral-400"
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
                                <span class="font-semibold text-sm text-neutral-800">QRIS</span>
                            </button>
                        </div>

                        <!-- Divider line -->
                        <div class="border-b border-neutral-100 my-6"></div>

                        <!-- Method : Tunai Section -->
                        <div id="methodTunaiContent" class="space-y-4">
                            <h3 class="text-sm sm:text-base font-bold text-neutral-900 tracking-tight">
                                Pembayaran Tunai
                            </h3>

                            <div>
                                <label class="block text-xs text-neutral-500 font-normal mb-1.5">
                                    Uang diterima
                                </label>
                                <input 
                                    type="text" 
                                    id="inputPaymentReceived" 
                                    placeholder="Rp 0" 
                                    class="w-full px-4 py-3 bg-white border border-neutral-200 rounded-xl text-sm sm:text-base font-bold text-neutral-900 focus:outline-none focus:border-neutral-900 transition"
                                >
                            </div>

                            <!-- Quick Amount Suggestions Pills-->
                            <div class="flex flex-wrap items-center gap-2.5 pt-1" id="quickAmountWrapper">
                                <button type="button" id="btnUangPasQuick" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer">
                                    Uang pas
                                </button>
                                <button type="button" class="btn-quick-pill px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer" data-val="466050000">
                                    Rp466.050.000
                                </button>
                                <button type="button" class="btn-quick-pill px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer" data-val="466100000">
                                    Rp466.100.000
                                </button>
                                <button type="button" class="btn-quick-pill px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer" data-val="500000000">
                                    Rp500.000.000
                                </button>
                            </div>

                            <!-- Kembalian Box -->
                            <div class="bg-neutral-100/90 rounded-xl px-4 py-3.5 flex items-center justify-between mt-4">
                                <span class="text-xs sm:text-sm font-semibold text-neutral-800">Kembalian</span>
                                <span id="paymentKembalianText" class="text-xs sm:text-sm font-bold text-[#00A854]">Rp0</span>
                            </div>

                            <!-- Validasi Pembayaran Button -->
                            <div class="pt-4">
                                <button 
                                    type="button" 
                                    id="btnValidatePayment" 
                                    class="px-6 py-3 rounded-xl text-xs sm:text-sm font-bold transition duration-150 shadow-xs bg-neutral-300 text-white cursor-not-allowed"
                                >
                                    Validasi Pembayaran
                                </button>
                            </div>
                        </div>

                        <!-- Method : Debit -->
                        <div id="methodDebitContent" class="hidden space-y-3">
                            <h3 class="text-sm sm:text-base font-bold text-neutral-900 tracking-tight">
                                Pembayaran Debit
                            </h3>
                            <p class="border border-amber-300 bg-amber-50 rounded-md px-2 py-2 text-xs text-neutral-800">Lanjutkan pembayaran di mesin EDC.</p>
                            <div class="pt-1">
                                <button type="button" id="btnValidateDebit" class="px-4 py-1.5 rounded-lg text-xs font-medium bg-neutral-900 text-white hover:bg-neutral-800 transition cursor-pointer">
                                    Pembayaran Berhasil
                                </button>
                            </div>
                        </div>

                        <!-- Method : qris-->
                        <div id="methodQRISContent" class="hidden space-y-3">
                            <h3 class="text-sm sm:text-base font-bold text-neutral-900 tracking-tight">
                                Pembayaran QRIS
                            </h3>
                            <p class="border border-amber-300 bg-amber-50 rounded-md px-2 py-2 text-xs text-neutral-800">Lanjutkan pembayaran di mesin QRIS.</p>
                            <div class="pt-1">
                                <button type="button" id="btnValidateQRIS" class="px-4 py-1.5 rounded-lg text-xs font-medium bg-neutral-900 text-white hover:bg-neutral-800 transition cursor-pointer">
                                    Pembayaran Berhasil
                                </button>
                            </div>
                        </div>

                    </div>
                </section>

                <!--RIGHT AREA: DETAIL ORDER-->
                <aside 
                    id="paymentOrderDetailsSidebar"
                    class="w-full lg:w-[410px] xl:w-[430px] shrink-0 bg-white border-t lg:border-t-0 lg:border-l border-neutral-200/90 flex flex-col justify-between h-auto lg:h-screen lg:sticky lg:top-0 z-20"
                >
                    <!-- Header with ✕ close button -->
                    <div class="flex items-center justify-between px-6 py-5 border-b border-neutral-100/90">
                        <h2 class="text-base font-bold text-neutral-900 tracking-tight">Detail Order</h2>
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
                        class="flex-1 overflow-y-auto px-6 py-4 space-y-4 max-h-[50vh] lg:max-h-none"
                    >
                        <!-- Rendered via JavaScript -->
                    </div>

                    <!-- Summary & Ubah Order Button -->
                    <div class="p-6 border-t border-neutral-100 bg-white space-y-2.5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-500 font-normal">Total Item</span>
                            <span id="paymentTotalItem" class="font-normal text-neutral-900">70 pcs</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-500 font-normal">Subtotal</span>
                            <span id="paymentSubtotal" class="font-normal text-neutral-900">Rp419.850.000</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-neutral-500 font-normal">Diskon</span>
                            <span id="paymentDiskon" class="font-normal text-neutral-900">Rp0</span>
                        </div>

                        <!-- Dashed line -->
                        <div class="border-b border-dashed border-neutral-200 my-2"></div>

                        <!-- Total -->
                        <div class="flex items-center justify-between pt-0.5">
                            <span class="text-base font-bold text-neutral-900 tracking-tight">Total</span>
                            <span id="paymentTotal" class="text-lg font-bold text-neutral-900 tracking-tight">Rp466.033.500</span>
                        </div>

                        <!-- Ubah Order Button (Kembali ke Katalog) -->
                        <button 
                            type="button" 
                            id="btnBackToOrder" 
                            class="w-full mt-3 py-3 border border-neutral-300 hover:bg-neutral-50 active:scale-[0.98] text-neutral-900 text-sm font-semibold rounded-xl transition duration-150 shadow-xs flex items-center justify-center cursor-pointer"
                        >
                            Ubah Order
                        </button>
                    </div>
                </aside>

            </div>

            <!--VIEW RECEIPT -->
            <div id="viewReceipt" class="hidden flex-1 flex flex-col lg:flex-row bg-white transition-all">
                
                <!-- LEFT/CENTER AREA: THERMAL RECEIPT & ACTION BUTTONS -->
                <section class="flex-1 min-w-0 p-5 sm:p-7 lg:p-8 overflow-y-auto max-h-screen bg-white">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 tracking-tight leading-none mb-7">
                        Detail Pesanan
                    </h1>

                    <!--Paper Thermal Struk Card-->
                    <div 
                        id="printableReceipt" 
                        class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6 sm:p-8 max-w-md w-full text-xs font-normal text-neutral-800"
                    >
                        <!-- Receipt Top Brand -->
                        <div class="text-center">
                            <div class="text-base font-bold text-neutral-900 tracking-tight">KasirAja</div>
                            <div class="text-[11px] text-neutral-500 mt-0.5">Toko Sepatu · Struk Pembelian</div>
                        </div>

                        <div class="border-b border-dashed border-neutral-200 my-4"></div>

                        <!-- Meta Info: No Nota, Tanggal, Kasir -->
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-neutral-500">No. nota</span>
                                <span id="receiptNoNota" class="font-bold text-neutral-900">TRX-20261120-003</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Tanggal</span>
                                <span id="receiptTanggal" class="font-normal text-neutral-800">20 Nov 2026, 14:56</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Kasir</span>
                                <span id="receiptKasir" class="font-normal text-neutral-800">Kasir 1</span>
                            </div>
                        </div>

                        <div class="border-b border-dashed border-neutral-200 my-4"></div>

                        <!-- Purchased Items List -->
                        <div id="receiptItemsContainer" class="space-y-3.5">
                            <!-- Items injected dynamically via JS -->
                        </div>

                        <div class="border-b border-dashed border-neutral-200 my-4"></div>

                        <!-- Subtotal, Pajak, Diskon -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-neutral-500" id="receiptSubtotalLabel">Subtotal (70 pasang)</span>
                                <span id="receiptSubtotalVal" class="font-medium text-neutral-900">Rp466.033.500</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Diskon</span>
                                <span id="receiptDiskonVal" class="font-medium text-neutral-900">Rp0</span>
                            </div>
                        </div>

                        <div class="border-b border-dashed border-neutral-200 my-4"></div>

                        <!-- Total Bayar, Metode, Jumlah Bayar, Kembalian -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between text-sm font-bold text-neutral-900">
                                <span>Total bayar</span>
                                <span id="receiptTotalBayarVal">Rp466.033.500</span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-neutral-500">Metode</span>
                                <span id="receiptMetodeVal" class="font-medium text-neutral-800">Tunai</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Jumlah Bayar</span>
                                <span id="receiptJumlahBayarVal" class="font-medium text-neutral-800">Rp466.100.000</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-neutral-500">Kembalian</span>
                                <span id="receiptKembalianVal" class="font-medium text-neutral-800">Rp66.500</span>
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
                            class="flex-1 py-3 px-4 bg-black hover:bg-neutral-800 active:scale-[0.98] text-white text-xs sm:text-sm font-bold rounded-xl transition duration-150 shadow-sm flex items-center justify-center cursor-pointer"
                        >
                            Unduh Struk (PDF)
                        </button>
                        <button 
                            type="button" 
                            id="btnCopyReceipt" 
                            class="flex-1 py-3 px-4 border border-neutral-300 hover:bg-neutral-50 active:scale-[0.98] text-neutral-900 text-xs sm:text-sm font-semibold rounded-xl transition duration-150 shadow-xs flex items-center justify-center cursor-pointer"
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
                            class="w-full py-3.5 border border-neutral-300 hover:bg-neutral-50 active:scale-[0.98] text-neutral-900 text-sm font-semibold rounded-xl transition duration-150 shadow-xs flex items-center justify-center cursor-pointer"
                        >
                            Transaksi Baru
                        </button>
                    </div>
                </aside>

            </div>

        </div>
    </div>

    <!-- POPUP PILIH UKURAN -->
    <div id="sizeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4" role="dialog" aria-modal="true" aria-labelledby="sizeModalTitle">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-5">
            <div class="flex items-start gap-3">
                <div class="w-16 h-16 rounded-xl bg-neutral-100 overflow-hidden shrink-0">
                    <img id="sizeModalImage" src="" alt="" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <h3 id="sizeModalTitle" class="text-sm font-bold text-neutral-900 leading-snug">Pilih Ukuran</h3>
                    <p id="sizeModalPrice" class="text-xs font-semibold text-neutral-700 mt-1"></p>
                    <p class="text-[11px] text-neutral-500 mt-0.5">Pilih ukuran yang dibeli pelanggan</p>
                </div>
                <button type="button" id="sizeModalClose" class="text-neutral-400 hover:text-neutral-900 text-xl leading-none cursor-pointer" aria-label="Tutup">&times;</button>
            </div>
            <div id="sizeModalOptions" class="mt-4 grid grid-cols-3 gap-2"></div>
            <div class="mt-4 flex items-center justify-between gap-3">
                <div class="flex items-center border border-neutral-300 rounded-lg text-sm font-semibold">
                    <button type="button" id="sizeQtyMinus" class="px-3 py-2 cursor-pointer select-none">-</button>
                    <span id="sizeQtyVal" class="w-8 text-center select-none">1</span>
                    <button type="button" id="sizeQtyPlus" class="px-3 py-2 cursor-pointer select-none">+</button>
                </div>
                <button type="button" id="sizeModalAdd" disabled class="flex-1 py-2.5 rounded-lg text-xs font-semibold text-white bg-neutral-300 cursor-not-allowed transition">
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
                        <div class="flex items-center justify-between gap-3 group">
                            <div class="w-14 h-12 rounded-lg ${bgBox} border border-neutral-100 flex items-center justify-center p-1 shrink-0 overflow-hidden">
                                <img src="${item.image}" alt="${item.name}" class="h-full w-full object-contain pointer-events-none" onerror="this.src='{{ asset("images/shoes/placeholder.svg") }}'">
                            </div>

                            <div class="flex-1 min-w-0 pr-1">
                                <h4 class="text-xs font-bold text-neutral-900 truncate leading-snug">${item.name}</h4>
                                <p class="text-[11px] text-neutral-500 font-normal mt-0.5">${varText}</p>
                                <p class="text-[11px] font-semibold text-neutral-900 mt-0.5">Rp${formatRupiah(item.price)}</p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <div class="flex items-center border border-neutral-300 rounded-md px-2 py-0.5 text-xs font-semibold text-neutral-800 gap-2.5">
                                    <button type="button" class="btn-step-minus hover:text-neutral-600 select-none cursor-pointer" data-index="${index}">-</button>
                                    <span class="w-5 text-center select-none text-[11px]">${item.qty}</span>
                                    <button type="button" class="btn-step-plus hover:text-neutral-600 select-none cursor-pointer" data-index="${index}">+</button>
                                </div>
                                <button type="button" class="btn-remove-item text-neutral-400 hover:text-red-500 transition cursor-pointer p-0.5" data-index="${index}" title="Hapus">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                            <div class="flex items-center justify-between gap-3 group">
                                <div class="w-14 h-12 rounded-lg ${bgBox} border border-neutral-100 flex items-center justify-center p-1 shrink-0 overflow-hidden">
                                    <img src="${item.image}" alt="${item.name}" class="h-full w-full object-contain pointer-events-none" onerror="this.src='{{ asset("images/shoes/placeholder.svg") }}'">
                                </div>

                                <div class="flex-1 min-w-0 pr-1">
                                    <h4 class="text-xs font-bold text-neutral-900 truncate leading-snug">${item.name}</h4>
                                    <p class="text-[11px] text-neutral-500 font-normal mt-0.5">${varText}</p>
                                    <p class="text-[11px] font-semibold text-neutral-900 mt-0.5">Rp${formatRupiah(item.price)}</p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <div class="flex items-center border border-neutral-300 rounded-md px-2 py-0.5 text-xs font-semibold text-neutral-800 gap-2.5">
                                        <button type="button" class="btn-step-minus-pay hover:text-neutral-600 select-none cursor-pointer" data-index="${index}">-</button>
                                        <span class="w-5 text-center select-none text-[11px]">${item.qty}</span>
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
                    <button type="button" id="btnUangPasQuick" class="px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer">
                        Uang pas
                    </button>
                    <button type="button" class="btn-quick-pill px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer" data-val="${opt1}">
                        Rp${formatRupiah(opt1)}
                    </button>
                    <button type="button" class="btn-quick-pill px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer" data-val="${opt2}">
                        Rp${formatRupiah(opt2)}
                    </button>
                    <button type="button" class="btn-quick-pill px-3.5 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white hover:bg-neutral-100 text-neutral-800 transition cursor-pointer" data-val="${opt3}">
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
                    btnValidate.className = 'px-6 py-3 rounded-xl text-xs sm:text-sm font-bold transition duration-150 shadow-xs bg-neutral-900 text-white hover:bg-neutral-800 active:scale-[0.98] cursor-pointer';
                } else {
                    btnValidate.className = 'px-6 py-3 rounded-xl text-xs sm:text-sm font-bold transition duration-150 shadow-xs bg-neutral-300 text-white cursor-not-allowed';
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
                addBtn.className = 'flex-1 py-2.5 rounded-lg text-xs font-semibold text-white bg-black hover:bg-neutral-800 cursor-pointer transition';
                addBtn.textContent = `Tambah · Rp${formatRupiah(modalProduct.price * modalQty)}`;
            } else {
                addBtn.disabled = true;
                addBtn.className = 'flex-1 py-2.5 rounded-lg text-xs font-semibold text-white bg-neutral-300 cursor-not-allowed transition';
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
                    return `<button type="button" class="size-opt border rounded-lg py-2 px-1 text-center transition ${habis ? 'border-neutral-200 bg-neutral-50 text-neutral-300 cursor-not-allowed' : 'border-neutral-300 hover:border-neutral-900 cursor-pointer'}"
                                data-id="${v.id}" ${habis ? 'disabled' : ''}>
                                <span class="block text-sm font-bold">${v.ukuran}</span>
                                <span class="block text-[10px] ${habis ? '' : 'text-neutral-500'}">${v.warna}</span>
                                <span class="block text-[10px] ${habis ? '' : 'text-neutral-500'}">${habis ? 'Habis' : 'Stok ' + sisa}</span>
                            </button>`;
                }).join('');
                wrap.querySelectorAll('.size-opt:not([disabled])').forEach(opt => {
                    opt.addEventListener('click', () => {
                        wrap.querySelectorAll('.size-opt').forEach(o => o.classList.remove('border-2', 'border-neutral-900', 'bg-neutral-100'));
                        opt.classList.add('border-2', 'border-neutral-900', 'bg-neutral-100');
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
