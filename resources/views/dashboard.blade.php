<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FBFBFB]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard Penjualan & Inventaris - KasirAja</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CDN Fallback & Vite -->
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
            /* Width 1/18 dari layar (100vw / 18) dengan batas minimum 64px */
            --sidebar-width: max(64px, calc(100vw / 18));
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #FBFBFB;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F1F1;
        }
        ::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }
    </style>
</head>
<body class="min-h-screen text-neutral-900 antialiased selection:bg-neutral-900 selection:text-white">

    <div class="relative w-full min-h-screen flex">
        
        <!-- ========================================== -->
        <!-- SIDEBAR (Fixed, Height 100vh, Width 1/18) -->
        <!-- ========================================== -->
        <aside 
            class="fixed top-0 left-0 bottom-0 z-50 bg-black flex flex-col items-center py-5 transition-all select-none shadow-xl"
            style="width: var(--sidebar-width); height: 100vh;"
            aria-label="Sidebar Menu"
        >
            <!-- Top Logo KasirAja -->
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-lg flex items-center justify-center" title="KasirAja">
                <img src="{{ asset('images/logo-white.svg') }}" alt="KasirAja" class="w-full h-full object-contain">
            </div>

            <!-- Divider Line -->
            <div class="w-7 sm:w-8 h-[1px] bg-neutral-800 my-5"></div>

            <!-- Navigation Menu Icons -->
            <nav class="flex flex-col items-center gap-4 w-full">
                <!-- 1. Dashboard (Active) -->
                <a 
                    href="{{ route('dashboard') }}" 
                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center text-white bg-neutral-900 hover:bg-neutral-800 transition group"
                    title="Dashboard"
                >
                    <!-- Rounded 3-block grid icon (matches Figma screenshot) -->
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="18" rx="2"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="2"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="2"></rect>
                    </svg>
                </a>

                <!-- 2. Kasir / POS -->
                <a 
                    href="#" 
                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center text-neutral-400 hover:text-white hover:bg-neutral-900 transition group"
                    title="Kasir / Transaksi"
                >
                    <!-- Cash Register Icon (matches Figma screenshot) -->
                    <svg class="w-6 h-6 text-neutral-400 group-hover:text-white transition" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Top display -->
                        <rect x="7" y="3" width="14" height="4" rx="1.5"></rect>
                        <path d="M12 7v2"></path>
                        <path d="M16 7v2"></path>
                        <!-- Main register body -->
                        <path d="M5 9h18a2 2 0 0 1 2 2l-1.5 10a2 2 0 0 1-2 1.8H6.5a2 2 0 0 1-2-1.8L3 11a2 2 0 0 1 2-2z"></path>
                        <!-- Key dots -->
                        <circle cx="9" cy="13" r="1" fill="currentColor"></circle>
                        <circle cx="14" cy="13" r="1" fill="currentColor"></circle>
                        <circle cx="19" cy="13" r="1" fill="currentColor"></circle>
                        <circle cx="11.5" cy="16.5" r="1" fill="currentColor"></circle>
                        <circle cx="16.5" cy="16.5" r="1" fill="currentColor"></circle>
                        <!-- Cash drawer slot -->
                        <path d="M7 20h14"></path>
                    </svg>
                </a>
            </nav>

            <!-- Bottom: Logout Button (always pinned at the bottom) -->
            <div class="mt-auto mb-3">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button 
                        type="submit" 
                        class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center text-neutral-400 hover:text-red-400 hover:bg-neutral-900 transition group cursor-pointer"
                        title="Keluar / Logout"
                    >
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- ========================================== -->
        <!-- MAIN CONTENT AREA (Offset by 1/18 width)  -->
        <!-- ========================================== -->
        <main 
            class="min-h-screen flex-1 bg-[#FBFBFB] transition-all"
            style="margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width));"
        >
            <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-10 py-6 sm:py-8 space-y-6">

                <!-- 1. TOP HEADER BAR -->
                <header class="flex flex-col md:flex-row md:items-center justify-between pb-6 border-b border-neutral-200/90 gap-4">
                    <div>
                        <h1 class="text-xl sm:text-2xl lg:text-[26px] font-extrabold text-neutral-900 tracking-tight leading-snug">
                            Dashboard Penjualan & Inventaris
                        </h1>
                        <p class="text-xs sm:text-sm text-neutral-500 font-normal mt-1">
                            Ringkasan performa penjualan harian, stok gudang & transaksi kasir real-time dari Toko.
                        </p>
                    </div>

                    <!-- User Badge & Filter Dropdown -->
                    <div class="flex items-center gap-3">
                        @auth
                            <div class="flex items-center gap-2.5 px-3 py-1.5 bg-neutral-100 rounded-xl border border-neutral-200">
                                <div class="w-8 h-8 rounded-lg bg-black text-white flex items-center justify-center text-xs font-bold uppercase">
                                    {{ substr(Auth::user()->nama_lengkap ?? Auth::user()->username, 0, 1) }}
                                </div>
                                <div class="text-left">
                                    <div class="text-xs font-bold text-neutral-900 leading-tight">{{ Auth::user()->nama_lengkap ?? Auth::user()->username }}</div>
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold uppercase tracking-wider {{ Auth::user()->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ Auth::user()->role }}
                                    </span>
                                </div>
                            </div>
                        @endauth

                        <div class="relative inline-block text-left">
                            <button 
                                type="button" 
                                class="inline-flex items-center gap-2.5 px-4 py-2 border border-neutral-300 rounded-xl text-xs sm:text-sm font-semibold text-neutral-800 bg-white hover:bg-neutral-50 transition cursor-pointer"
                            >
                                <span>Bulan Ini</span>
                                <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </header>

                <!-- 2. ROW OF 4 SUMMARY METRIC CARDS -->
                <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    
                    <!-- Card 1: TOTAL PENJUALAN -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-900 uppercase">
                                    TOTAL PENJUALAN
                                </span>
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <!-- Banknote Icon -->
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                        <path d="M6 12h.01M18 12h.01"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-xl sm:text-2xl font-black text-neutral-900 tracking-tight">
                                Rp{{ number_format($stats['total_penjualan'] ?? 435400000, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#E6F9F0] text-[#00A854]">
                                ↗ +{{ $stats['persen_penjualan'] ?? '14.8' }}%
                            </span>
                            <span class="text-[11px] text-neutral-400 font-medium ml-2">
                                vs bulan sebelumnya
                            </span>
                        </div>
                    </div>

                    <!-- Card 2: TOTAL TRANSAKSI -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-900 uppercase">
                                    TOTAL TRANSAKSI
                                </span>
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <!-- Receipt Ticket Icon -->
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-xl sm:text-2xl font-black text-neutral-900 tracking-tight">
                                {{ $stats['total_transaksi'] ?? 3 }} Struk
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600">
                                Rp{{ number_format($stats['rata_rata_nota'] ?? 145133333, 0, ',', '.') }}
                            </span>
                            <span class="text-[11px] text-neutral-400 font-medium ml-2">
                                Rata-rata per nota
                            </span>
                        </div>
                    </div>

                    <!-- Card 3: TOTAL ITEM TERJUAL -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-900 uppercase">
                                    TOTAL ITEM TERJUAL
                                </span>
                                <div class="w-9 h-9 rounded-xl bg-[#E6F9F0] text-emerald-600 flex items-center justify-center">
                                    <!-- Shoe / Box Icon -->
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-xl sm:text-2xl font-black text-neutral-900 tracking-tight">
                                {{ $stats['total_terjual'] ?? 71 }} Pasang
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#E6F9F0] text-[#00A854]">
                                ↗ +{{ $stats['persen_penjualan'] ?? '14.8' }}%
                            </span>
                            <span class="text-[11px] text-neutral-400 font-medium ml-2">
                                Target bulanan tercapai
                            </span>
                        </div>
                    </div>

                    <!-- Card 4: PERINGATAN STOK -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-900 uppercase">
                                    PERINGATAN STOK
                                </span>
                                <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                                    <!-- Warning Alert Icon -->
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-2 text-xl sm:text-2xl font-black text-red-600 tracking-tight">
                                {{ $stats['peringatan_stok'] ?? 1 }} Varian
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-end">
                            <a href="#katalog-stok" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                Lihat detail →
                            </a>
                        </div>
                    </div>

                </section>

                <!-- 3. MIDDLE SECTION: TREN PENJUALAN BULANAN & KATEGORI TERLARIS + RESTOCK -->
                <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    
                    <!-- Left: Tren Penjualan Bulanan (8 cols on XL, 7 cols on LG) -->
                    <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-2xl border border-neutral-200/90 p-5 sm:p-6 flex flex-col justify-between shadow-sm">
                        <div>
                            <h2 class="text-base font-bold text-neutral-900 tracking-tight">
                                Tren Penjualan Bulanan
                            </h2>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                Perbandingan volume penjualan per-bulan.
                            </p>
                        </div>

                        <!-- Bar Chart Graphic -->
                        <div class="h-64 sm:h-72 flex items-end justify-between gap-1.5 sm:gap-3 pt-8 pb-2 px-1">
                            @php
                                $months = $monthlySales ?? [
                                    ['month' => 'Nov', 'height' => 52, 'amount' => 'Rp 226,4jt'],
                                    ['month' => 'Des', 'height' => 70, 'amount' => 'Rp 305,2jt'],
                                    ['month' => 'Jan', 'height' => 48, 'amount' => 'Rp 209,1jt'],
                                    ['month' => 'Feb', 'height' => 36, 'amount' => 'Rp 156,8jt'],
                                    ['month' => 'Mar', 'height' => 44, 'amount' => 'Rp 191,5jt'],
                                    ['month' => 'Apr', 'height' => 60, 'amount' => 'Rp 261,3jt'],
                                    ['month' => 'Mei', 'height' => 64, 'amount' => 'Rp 278,7jt'],
                                    ['month' => 'Jun', 'height' => 48, 'amount' => 'Rp 209,0jt'],
                                    ['month' => 'Jul', 'height' => 68, 'amount' => 'Rp 296,2jt'],
                                    ['month' => 'Agu', 'height' => 82, 'amount' => 'Rp 357,0jt'],
                                    ['month' => 'Sep', 'height' => 74, 'amount' => 'Rp 322,2jt'],
                                    ['month' => 'Okt', 'height' => 96, 'amount' => 'Rp 435,4jt'],
                                ];
                            @endphp

                            @foreach ($months as $bar)
                                <div class="flex-1 flex flex-col items-center h-full justify-end group relative cursor-pointer">
                                    <!-- Tooltip hover -->
                                    <div class="absolute -top-8 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none bg-neutral-900 text-white text-[10px] font-semibold px-2 py-1 rounded shadow whitespace-nowrap z-10">
                                        {{ $bar['month'] }}: {{ $bar['amount'] }}
                                    </div>
                                    <!-- Vertical Bar -->
                                    <div 
                                        class="w-full max-w-[28px] sm:max-w-[34px] bg-[#0052FF] group-hover:bg-[#003ecb] rounded-t-md transition-all duration-300"
                                        style="height: {{ $bar['height'] }}%;"
                                    ></div>
                                    <!-- Month Label -->
                                    <span class="text-[11px] sm:text-xs font-semibold text-neutral-800 text-center mt-3 select-none">
                                        {{ $bar['month'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- 3 Summary Pills at bottom of chart card -->
                        <div class="grid grid-cols-3 gap-3 sm:gap-4 mt-6 pt-5" style="padding-top: 0px">
                            <!-- Box 1 -->
                            <div class="border border-neutral-400/80 rounded-xl p-3 sm:p-4 text-center">
                                <div class="text-sm sm:text-base font-extrabold text-neutral-900">
                                    Okt
                                </div>
                                <div class="text-[11px] sm:text-xs text-neutral-500 mt-0.5">
                                    Puncak - Rp 435,4jt
                                </div>
                            </div>
                            <!-- Box 2 -->
                            <div class="border border-neutral-400/80 rounded-xl p-3 sm:p-4 text-center">
                                <div class="text-sm sm:text-base font-extrabold text-neutral-900">
                                    Rp 331,5jt
                                </div>
                                <div class="text-[11px] sm:text-xs text-neutral-500 mt-0.5">
                                    Rata-rata/bln
                                </div>
                            </div>
                            <!-- Box 3 -->
                            <div class="border border-neutral-400/80 rounded-xl p-3 sm:p-4 text-center">
                                <div class="text-sm sm:text-base font-extrabold text-neutral-900">
                                    +22%
                                </div>
                                <div class="text-[11px] sm:text-xs text-neutral-500 mt-0.5">
                                    Proyeksi Q4 YoY
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Kategori Terlaris & Perlu Restock Segera (4 cols on XL, 5 cols on LG) -->
                    <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-5">
                        
                        <!-- Card: Kategori Terlaris -->
                        <div class="bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-sm font-bold text-neutral-900 tracking-tight">
                                    Kategori Terlaris
                                </h2>
                                <a href="#" class="text-xs font-semibold text-neutral-800 hover:text-black transition">
                                    Lihat semua →
                                </a>
                            </div>

                            <div class="space-y-4">
                                @php
                                    $categories = $topCategories ?? [
                                        ['name' => 'Lifestyle', 'count' => 67, 'percent' => 94],
                                        ['name' => 'Running',   'count' => 2,  'percent' => 3],
                                        ['name' => 'Sports',    'count' => 1,  'percent' => 2],
                                        ['name' => 'Casual',    'count' => 1,  'percent' => 1],
                                    ];
                                @endphp

                                @foreach ($categories as $cat)
                                    <div>
                                        <div class="flex items-center justify-between text-xs font-medium text-neutral-900 mb-1.5">
                                            <span>{{ $cat['name'] }}</span>
                                            <span class="font-bold text-neutral-800">({{ $cat['count'] }} PSG) - {{ $cat['percent'] }}%</span>
                                        </div>
                                        <div class="w-full bg-neutral-100 h-2 rounded-full overflow-hidden">
                                            <div class="bg-[#0052FF] h-full rounded-full" style="width: {{ $cat['percent'] }}%;"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Card: Perlu Restock Segera -->
                        <div class="bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-3">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0"></span>
                                    <h2 class="text-sm font-bold text-neutral-900 tracking-tight">
                                        Perlu Restock Segera
                                    </h2>
                                </div>

                                <div class="divide-y divide-black/10">
                                    @php
                                        $restocks = $restockAlerts ?? [
                                            ['name' => 'FayerJyordan', 'detail' => 'Size 44 EUR - Hitam - sisa 0, min. 10', 'status' => 'Kritis'],
                                            ['name' => 'ErthJyordan',  'detail' => 'Size 40 EUR - Abu - sisa 4, min. 5',   'status' => 'Kritis'],
                                            ['name' => 'LoremIpsum',   'detail' => 'LoremIpsum1234567890',                'status' => 'Kritis'],
                                        ];
                                    @endphp

                                    @foreach ($restocks as $alert)
                                        <div class="py-3 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                            <div>
                                                <div class="text-xs font-bold text-neutral-900">
                                                    {{ $alert['name'] }}
                                                </div>
                                                <div class="text-[11px] text-neutral-500 mt-0.5">
                                                    {{ $alert['detail'] }}
                                                </div>
                                            </div>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600 border border-red-100 whitespace-nowrap">
                                                {{ $alert['status'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>

                </section>

                <!-- 4. SECTION: MARGIN UNTUNG PER KATEGORI & STOK MENUMPUK -->
                <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    
                    <!-- Left: Margin Untung per Kategori -->
                    <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm">
                        <h2 class="text-sm font-bold text-neutral-900 tracking-tight mb-3">
                            Margin Untung per Kategori
                        </h2>

                        <div class="divide-y divide-black/10">
                            @php
                                $margins = $categoryMargins ?? [
                                    ['category' => 'Lifestyle', 'margin' => '34%', 'is_highlight' => true],
                                    ['category' => 'Running',   'margin' => '21%', 'is_highlight' => false],
                                    ['category' => 'Sports',    'margin' => '25%', 'is_highlight' => false],
                                    ['category' => 'Casual',    'margin' => '41%', 'is_highlight' => true],
                                ];
                            @endphp

                            @foreach ($margins as $m)
                                <div class="py-2.5 first:pt-1 last:pb-1 flex items-center justify-between text-xs sm:text-sm">
                                    <span class="text-neutral-800 font-medium">{{ $m['category'] }}</span>
                                    <span class="font-bold {{ $m['is_highlight'] ? 'text-emerald-600' : 'text-neutral-900' }}">
                                        {{ $m['margin'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right: Stok Menumpuk / Slow-Moving -->
                    <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm flex flex-col justify-start">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <h2 class="text-sm font-bold text-neutral-900 tracking-tight">
                                Stok Menumpuk / Slow-Moving
                            </h2>
                        </div>

                        <div class="divide-y divide-black/10">
                            @php
                                $slowItems = $slowMoving ?? [
                                    [
                                        'name' => 'WaterJyordan',
                                        'detail' => 'Size 40 - 41 - 42 - 43 - 44 EUR - Merah - Hitam',
                                        'note' => 'Sisa <strong>66 - 1</strong> terjual bulan ini',
                                        'status' => 'Overstock',
                                    ],
                                ];

                                if (isset($slowItems['name'])) {
                                    $slowItems = [$slowItems];
                                }
                            @endphp

                            @foreach ($slowItems as $item)
                                <div class="py-3 first:pt-1 last:pb-1 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900">
                                            {{ $item['name'] }}
                                        </div>
                                        <div class="text-[11px] text-neutral-500 mt-0.5">
                                            {{ $item['detail'] }}
                                        </div>
                                        @if (!empty($item['note']))
                                            <div class="text-[11px] text-neutral-500 mt-1">
                                                {!! $item['note'] !!}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">
                                        {{ $item['status'] ?? 'Overstock' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </section>

                <!-- 5. SECTION: KATALOG STOK SEPATU (DATA TABLE) -->
                <section id="katalog-stok" class="bg-white rounded-2xl border border-neutral-200/90 p-5 sm:p-6 shadow-sm mb-12">
                    
                    <!-- Table Title & Subtitle -->
                    <div>
                        <h2 class="text-base sm:text-lg font-bold text-neutral-900 tracking-tight">
                            Katalog Stok Sepatu
                        </h2>
                        <p class="text-xs text-neutral-500 mt-0.5">
                            Data real-time fisik gudang dan harga jual retail kasir.
                        </p>
                    </div>

                    <!-- Search Input & Filter Dropdown Bar -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mt-4">
                        <!-- Search Box with Icon -->
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-neutral-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="shoe-search" 
                                placeholder="Cari nama sepatu atau brand" 
                                class="w-full pl-10 pr-4 py-2 bg-white border border-neutral-200 rounded-xl text-xs sm:text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-400 transition"
                            />
                        </div>

                        <!-- Dropdown Filter -->
                        <div class="relative">
                            <button 
                                type="button" 
                                class="w-full sm:w-auto px-4 py-2 border border-neutral-200 rounded-xl   text-xs sm:text-sm font-medium text-neutral-800 bg-white flex items-center justify-between sm:justify-center gap-2 whitespace-nowrap cursor-pointer"
                            >
                                <span>Filter to &ldquo;Option 2&rdquo;</span>
                                <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Responsive Data Table -->
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs sm:text-sm">
                            <thead>
                                <tr class="border-b border-neutral-200 text-neutral-800 font-semibold text-xs">
                                    <th class="py-3 px-3 w-10 text-center"></th>
                                    <th class="py-3 px-4">Sepatu</th>
                                    <th class="py-3 px-4">Merek</th>
                                    <th class="py-3 px-4">Varian</th>
                                    <th class="py-3 px-4">Harga Satuan</th>
                                    <th class="py-3 px-4">Total Stok</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black/10" id="shoe-table-body">
                                @php
                                    $shoes = $shoeCatalog ?? [
                                        [
                                            'name' => 'WaterJyordan',
                                            'code' => 'kodeprdk01',
                                            'image' => 'images/shoes/water-jyordan.png',
                                            'brand' => 'Asuz',
                                            'variant' => '4 ukuran - 2 warna',
                                            'unit_price' => 'Rp 3.110.000',
                                            'total_stock' => '66 pasang',
                                            'status' => 'Overstock',
                                            'status_type' => 'overstock',
                                        ],
                                        [
                                            'name' => 'FayerJyordan',
                                            'code' => 'kodeprdk02',
                                            'image' => 'images/shoes/fayer-jyordan.png',
                                            'brand' => 'Zusa',
                                            'variant' => '4 ukuran - 1 warna',
                                            'unit_price' => 'Rp6.220.000',
                                            'total_stock' => '0 pasang',
                                            'status' => 'Habis',
                                            'status_type' => 'habis',
                                        ],
                                        [
                                            'name' => 'ErthJyordan',
                                            'code' => 'kodeprdk03',
                                            'image' => 'images/shoes/erth-jyordan.png',
                                            'brand' => 'Uzas',
                                            'variant' => '3 ukuran - 1 warna',
                                            'unit_price' => 'Rp6.220.000',
                                            'total_stock' => '5 pasang',
                                            'status' => 'Kritis',
                                            'status_type' => 'kritis',
                                        ],
                                        [
                                            'name' => 'WhinJyordan',
                                            'code' => 'kodeprdk04',
                                            'image' => 'images/shoes/whin-jyordan.png',
                                            'brand' => 'Uzuz',
                                            'variant' => '3 ukuran - 2 warna',
                                            'unit_price' => 'Rp6.220.000',
                                            'total_stock' => '10 pasang',
                                            'status' => 'Kritis',
                                            'status_type' => 'kritis',
                                        ],
                                    ];
                                @endphp

                                @foreach ($shoes as $item)
                                    <tr class="hover:bg-neutral-50/70 transition-colors shoe-row">
                                        <!-- Checkbox / Accordion Toggle -->
                                        <td class="py-3 px-3 text-center align-middle">
                                            <!-- <button type="button" class="w-5 h-5 rounded border border-neutral-300 flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:border-neutral-400 transition mx-auto">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                                </svg>
                                            </button> -->
                                        </td>

                                        <!-- Shoe Image & Name / Code -->
                                        <td class="py-3 px-4 align-middle">
                                            <div class="flex items-center gap-3">
                                                <div class="w-11 h-11 rounded-xl bg-neutral-50 border border-neutral-100 flex items-center justify-center p-1 flex-shrink-0 overflow-hidden shadow-xs">
                                                    <img 
                                                        src="{{ asset($item['image']) }}" 
                                                        alt="{{ $item['name'] }}" 
                                                        class="w-full h-full object-contain"
                                                        loading="lazy"
                                                    />
                                                </div>
                                                <div>
                                                    <div class="font-bold text-xs sm:text-sm text-neutral-900 shoe-name">
                                                        {{ $item['name'] }}
                                                    </div>
                                                    <div class="text-[11px] text-neutral-400 font-normal">
                                                        {{ $item['code'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Brand / Merek -->
                                        <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 shoe-brand font-medium">
                                            {{ $item['brand'] }}
                                        </td>

                                        <!-- Varian -->
                                        <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium">
                                            {{ $item['variant'] }}
                                        </td>

                                        <!-- Unit Price -->
                                        <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium">
                                            {{ $item['unit_price'] }}
                                        </td>

                                        <!-- Total Stock -->
                                        <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium">
                                            {{ $item['total_stock'] }}
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-3 px-4 align-middle text-center">
                                            @if ($item['status_type'] === 'overstock')
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A]">
                                                    {{ $item['status'] }}
                                                </span>
                                            @elseif ($item['status_type'] === 'habis')
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEE2E2] text-[#DC2626] border border-[#FECACA]">
                                                    {{ $item['status'] }}
                                                </span>
                                            @else
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEE2E2] text-[#DC2626] border border-[#FECACA]">
                                                    {{ $item['status'] }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer: Pagination & Counts -->
                    <div class="mt-4 pt-4 border-t border-neutral-100 flex flex-col sm:flex-row items-center justify-between text-xs text-neutral-500 gap-3">
                        <div>
                            Menampilkan <span class="font-semibold text-neutral-800">1-4</span> dari <span class="font-semibold text-neutral-800">**</span> produk
                        </div>
                        <div class="flex items-center gap-1 select-none font-medium text-neutral-700">
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100 text-neutral-500">&lsaquo;</button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded bg-neutral-900 text-white font-bold">1</button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100">2</button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100">3</button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100">4</button>
                            <span class="px-1 text-neutral-400">...</span>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100">**</button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100 text-neutral-500">&rsaquo;</button>
                        </div>
                    </div>

                </section>

            </div>
        </main>

    </div>

    <!-- Client-side Interactive Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('shoe-search');
            const rows = document.querySelectorAll('.shoe-row');

            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    const q = e.target.value.toLowerCase().trim();
                    rows.forEach(row => {
                        const name = row.querySelector('.shoe-name')?.textContent.toLowerCase() || '';
                        const brand = row.querySelector('.shoe-brand')?.textContent.toLowerCase() || '';
                        if (name.includes(q) || brand.includes(q)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>