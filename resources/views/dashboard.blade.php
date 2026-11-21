<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FBFBFB]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard Penjualan & Inventaris - KasirAja</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
                            sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
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
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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

        /* Realtime update glow highlight */
        .value-highlight {
            animation: highlightPulse 1.2s cubic-bezier(0.4, 0, 0.6, 1);
        }

        @keyframes highlightPulse {
            0% {
                background-color: rgba(16, 185, 129, 0.25);
                transform: scale(1.03);
            }
            100% {
                background-color: transparent;
                transform: scale(1);
            }
        }
    </style>
</head>
<body class="min-h-screen text-neutral-900 antialiased selection:bg-neutral-900 selection:text-white">

    <div class="relative w-full min-h-screen flex">
        
        <!-- SIDEBAR -->
        @include('layouts.sidebar')

        <!-- MAIN CONTENT AREA -->
        <main 
            class="min-h-screen flex-1 bg-[#FBFBFB] transition-all"
            style="margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width));"
        >
            <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-8 lg:px-10 py-6 sm:py-8 space-y-6">

                <!-- 1. TOP HEADER BAR (Sesuai Page Dashboard (2).png) -->
                <header class="flex flex-col lg:flex-row lg:items-center justify-between pb-6 border-b border-neutral-200/90 gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl sm:text-2xl lg:text-[26px] font-extrabold text-neutral-900 tracking-tight leading-snug">
                                Dashboard Penjualan & Inventaris
                            </h1>
                        </div>
                        <p class="text-xs sm:text-sm text-neutral-500 font-normal mt-1 flex items-center gap-2">
                            <span>Ringkasan performa penjualan harian, stok gudang & transaksi kasir real-time dari Toko.</span>
                        </p>
                    </div>

                    <!-- Right Controls: Administrator Toko Box (Sesuai Page Dashboard (2).png) -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-3 px-4 py-2 bg-white rounded-2xl border border-neutral-300 shadow-xs">
                            <div class="w-9 h-9 rounded-xl bg-black text-white flex items-center justify-center text-sm font-extrabold uppercase flex-shrink-0">
                                {{ substr(Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'A', 0, 1) }}
                            </div>
                            <div class="text-left">
                                <div class="text-xs sm:text-sm font-bold text-neutral-900 leading-snug">
                                    {{ Auth::user()->nama_lengkap ?? 'Administrator Toko' }}
                                </div>
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-[#F3E8FF] text-[#7E22CE] border border-purple-200/60">
                                    {{ strtoupper(Auth::user()->role ?? 'ADMIN') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </header>

                <!--ROW OF 4 SUMMARY METRIC CARDS-->
                <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                    
                    <!-- Card : TOTAL PENJUALAN -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-800 uppercase">
                                    TOTAL PENJUALAN
                                </span>
                                <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                        <path d="M6 12h.01M18 12h.01"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-total-penjualan" class="mt-2 text-2xl sm:text-3xl font-black text-neutral-900 tracking-tight transition-all duration-300">
                                {{ $stats['total_penjualan_formatted'] ?? 'Rp435.400.000' }}
                            </div>
                        </div>
                        <div class="mt-4 flex items-center">
                            <span id="stat-persen-penjualan" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#E6F9F0] text-[#00A854]">
                                Naik - 14.8%
                            </span>
                        </div>
                    </div>

                    <!-- Card : TOTAL ITEM TERJUAL -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-800 uppercase">
                                    TOTAL ITEM TERJUAL
                                </span>
                                <div class="w-10 h-10 rounded-xl bg-[#E6F9F0] text-[#00A854] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#00A854]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-total-terjual" class="mt-2 text-2xl sm:text-3xl font-black text-neutral-900 tracking-tight transition-all duration-300">
                                {{ $stats['total_terjual'] ?? 71 }} Pasang
                            </div>
                        </div>
                        <div class="mt-4 flex items-center">
                            <span id="stat-persen-terjual" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#E6F9F0] text-[#00A854]">
                                Target bulanan tercapai - ↗14.8%
                            </span>
                        </div>
                    </div>

                    <!-- Card : TOTAL TRANSAKSI -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-800 uppercase">
                                    TOTAL TRANSAKSI
                                </span>
                                <div class="w-10 h-10 rounded-xl bg-[#EFF6FF] text-[#2563EB] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#2563EB]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-total-transaksi" class="mt-2 text-2xl sm:text-3xl font-black text-neutral-900 tracking-tight transition-all duration-300">
                                {{ $stats['total_transaksi'] ?? 32 }} Struk
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-end">
                            {{-- Bersih sesuai visual Page Dashboard (2).png --}}
                        </div>
                    </div>

                    <!-- Card : PERINGATAN STOK -->
                    <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 shadow-[0_1px_3px_rgba(0,0,0,0.03)] flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold tracking-wider text-neutral-800 uppercase">
                                    PERINGATAN STOK
                                </span>
                                <div class="w-10 h-10 rounded-xl bg-[#FEF2F2] text-[#DC2626] flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#DC2626]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-peringatan-stok" class="mt-2 text-2xl sm:text-3xl font-black text-[#DC2626] tracking-tight transition-all duration-300">
                                {{ $stats['peringatan_stok'] ?? 3 }} Varian
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-end">
                            <a href="#katalog-stok" class="text-xs font-semibold text-[#2563EB] hover:underline flex items-center gap-1">
                                Lihat detail →
                            </a>
                        </div>
                    </div>

                </section>

                <!--DASHBOARD CHARTS AND INVENTORY PANELS-->
                <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    
                    <!-- Left: Tren Penjualan Bulanan (8 cols on XL, 7 cols on LG) -->
                    <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-2xl border border-neutral-200/90 p-5 sm:p-6 flex flex-col justify-between shadow-sm relative">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-neutral-900 tracking-tight">
                                    Tren Penjualan Bulanan
                                </h2>
                                <p class="text-xs text-neutral-500 mt-0.5">
                                    Perbandingan volume penjualan per-bulan.
                                </p>
                            </div>

                            <!-- Date Range Dropdown Button & Popover Modal (Sesuai Page Dashboard (2).png) -->
                            <div class="relative" id="chart-date-filter-wrapper">
                                <button 
                                    type="button" 
                                    id="btn-chart-date-filter"
                                    class="inline-flex items-center gap-2.5 px-3.5 py-2 border border-neutral-300 rounded-xl text-xs sm:text-sm font-semibold text-neutral-800 bg-white hover:bg-neutral-50 shadow-xs transition cursor-pointer"
                                >
                                    <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span id="chart-date-filter-label">Nov 2025 - Okt 2026</span>
                                    <svg id="chart-date-chevron" class="w-4 h-4 text-neutral-500 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                    </svg>
                                </button>

                                <!-- Interactive Popover Dropdown (Sesuai Page Dashboard (2).png) -->
                                <div 
                                    id="chart-date-popover" 
                                    class="hidden absolute right-0 top-12 z-40 bg-white rounded-2xl border border-neutral-200/90 shadow-2xl p-5 w-80 sm:w-96 text-left transition-all"
                                >
                                    <!--Rentang Cepat-->
                                    <div>
                                        <div class="text-[11px] font-bold text-neutral-500 uppercase tracking-wider mb-2.5">
                                            RENTANG CEPAT
                                        </div>
                                        <div class="flex flex-wrap gap-2" id="quick-range-chips">
                                            <button type="button" class="quick-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 transition cursor-pointer" data-range="30d">
                                                30 hari terakhir
                                            </button>
                                            <button type="button" class="quick-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 transition cursor-pointer" data-range="3m">
                                                3 bulan terakhir
                                            </button>
                                            <button type="button" class="quick-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 transition cursor-pointer" data-range="this_year">
                                                Tahun ini
                                            </button>
                                            <button type="button" class="quick-chip px-3.5 py-1.5 rounded-full text-xs font-semibold bg-neutral-900 text-white border border-neutral-900 shadow-xs cursor-pointer active-chip" data-range="12m">
                                                12 bulan terakhir
                                            </button>
                                            <button type="button" class="quick-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 transition cursor-pointer" data-range="all">
                                                Sejak toko buka
                                            </button>
                                        </div>
                                    </div>

                                    <!-- 2. Atur Sendiri -->
                                    <div class="mt-4">
                                        <div class="text-[11px] font-bold text-neutral-500 uppercase tracking-wider mb-2">
                                            ATUR SENDIRI
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="relative">
                                                <input 
                                                    type="text" 
                                                    id="date-input-start" 
                                                    value="01/11/2025" 
                                                    class="w-full pl-3 pr-8 py-2 border border-neutral-200 rounded-xl text-xs text-neutral-900 font-medium focus:outline-none focus:border-neutral-400 bg-white"
                                                />
                                                <svg class="w-4 h-4 text-neutral-700 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                                </svg>
                                            </div>
                                            <div class="relative">
                                                <input 
                                                    type="text" 
                                                    id="date-input-end" 
                                                    value="01/11/2025" 
                                                    class="w-full pl-3 pr-8 py-2 border border-neutral-200 rounded-xl text-xs text-neutral-900 font-medium focus:outline-none focus:border-neutral-400 bg-white"
                                                />
                                                <svg class="w-4 h-4 text-neutral-700 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!--Tampilkan Per-->
                                    <div class="mt-4">
                                        <div class="text-[11px] font-bold text-neutral-500 uppercase tracking-wider mb-2">
                                            TAMPILKAN PER
                                        </div>
                                        <div class="bg-neutral-100 p-1 rounded-xl flex items-center text-xs font-medium" id="display-per-segmented">
                                            <button type="button" class="flex-1 py-1.5 text-center text-neutral-600 rounded-lg hover:text-neutral-900 transition segmented-btn" data-type="hari">
                                                Hari
                                            </button>
                                            <button type="button" class="flex-1 py-1.5 text-center text-neutral-600 rounded-lg hover:text-neutral-900 transition segmented-btn" data-type="minggu">
                                                Minggu
                                            </button>
                                            <button type="button" class="flex-1 py-1.5 text-center text-neutral-900 font-bold bg-white rounded-lg shadow-xs transition segmented-btn active-segment" data-type="bulan">
                                                Bulan
                                            </button>
                                        </div>
                                    </div>

                                    <!--Footer-->
                                    <div class="mt-4 pt-3 border-t border-neutral-100 flex items-center justify-between">
                                        <span id="date-summary-text" class="text-xs text-neutral-500 font-normal">
                                            304 hari - 10 batang per bulan
                                        </span>
                                        <button 
                                            type="button" 
                                            id="btn-apply-date-filter"
                                            class="px-4 py-2 bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs rounded-xl shadow-xs transition cursor-pointer"
                                        >
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bar Chart Graphic (Royal Blue Bars, 12 Bulan) -->
                        <div id="monthly-sales-container" class="h-64 sm:h-72 flex items-end justify-between gap-1.5 sm:gap-3 pt-8 pb-2 px-1">
                            @php
                                $months = $monthlySales ?? [];
                            @endphp

                            @foreach ($months as $bar)
                                <div class="flex-1 flex flex-col items-center h-full justify-end group relative cursor-pointer">
                                    <!-- Tooltip hover -->
                                    <div class="absolute -top-8 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none bg-neutral-900 text-white text-[10px] font-semibold px-2 py-1 rounded shadow whitespace-nowrap z-10 chart-tooltip">
                                        {{ $bar['month'] }}: {{ $bar['amount'] }}
                                    </div>
                                    <!-- Vertical Bar -->
                                    <div 
                                        class="w-full max-w-[28px] sm:max-w-[34px] bg-[#0052FF] group-hover:bg-[#003ecb] rounded-t-md transition-all duration-500 chart-bar"
                                        style="height: {{ $bar['height'] }}%;"
                                    ></div>
                                    <!-- Month Label -->
                                    <span class="text-[11px] sm:text-xs font-semibold text-neutral-800 text-center mt-3 select-none chart-month">
                                        {{ $bar['month'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Single Full-Width Box below chart (Sesuai Page Dashboard (2).png) -->
                        <div class="border border-neutral-300 rounded-2xl p-4 text-center mt-6">
                            <div id="chart-peak-month" class="text-base font-extrabold text-neutral-900">
                                {{ $chartPills['peak_month'] ?? 'Okt' }}
                            </div>
                            <div id="chart-peak-amount" class="text-xs text-neutral-500 mt-0.5">
                                {{ $chartPills['peak_amount'] ?? 'Puncak - Rp 435,4jt' }}
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Inventory Alerts (Sesuai Page Dashboard (2).png) -->
                    <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-5 lg:h-full">
                        
                        <!-- Card : Perlu Restock Segera -->
                        <div class="bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0 animate-pulse"></span>
                                <h2 class="text-sm font-bold text-neutral-900 tracking-tight">
                                    Perlu Restock Segera
                                </h2>
                            </div>

                            <div id="restock-alerts-container" class="divide-y divide-neutral-100">
                                @php
                                    $restocks = $restockAlerts ?? [];
                                @endphp

                                @forelse ($restocks as $alert)
                                    <div class="py-3 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-xs font-bold text-neutral-900">
                                                {{ $alert['name'] }}
                                            </div>
                                            <div class="text-[11px] text-neutral-500 mt-0.5">
                                                {{ $alert['detail'] }}
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FEE2E2] text-[#DC2626] border border-[#FECACA] whitespace-nowrap">
                                            {{ $alert['status'] }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="py-4 text-center text-xs text-neutral-400">
                                        Semua stok aman dan mencukupi.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Card : Stok Menumpuk / Slow-Moving -->
                        <div class="bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                                <h2 class="text-sm font-bold text-neutral-900 tracking-tight">
                                    Stok Menumpuk / Slow-Moving
                                </h2>
                            </div>

                            <div id="slow-moving-container" class="divide-y divide-neutral-100">
                                @php
                                    $slowItems = $slowMoving ?? [];
                                    if (isset($slowItems['name'])) {
                                        $slowItems = [$slowItems];
                                    }
                                @endphp

                                @forelse ($slowItems as $item)
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
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A] whitespace-nowrap">
                                            {{ $item['status'] ?? 'Overstock' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="py-4 text-center text-xs text-neutral-400">
                                        Tidak ada stok menumpuk saat ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                </section>

                <!--KATALOG STOK SEPATU-->
                <section id="katalog-stok" class="w-full bg-white rounded-2xl border border-neutral-200/90 p-5 sm:p-6 shadow-sm">
                    
                    <!-- Search Input & Filter Dropdown Bar (Sesuai Page Dashboard (2).png) -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
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
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-neutral-300 rounded-xl text-xs sm:text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-500 transition"
                            />
                        </div>

                        <!-- Show [ 100 ⌵ ] Dropdown Button (Sesuai Page Dashboard (2).png) -->
                        <div class="relative" id="show-count-wrapper">
                            <button 
                                type="button" 
                                id="btn-show-count"
                                class="px-4 py-2.5 border border-neutral-300 rounded-xl text-xs sm:text-sm font-medium text-neutral-800 bg-white hover:bg-neutral-50 flex items-center gap-1.5 whitespace-nowrap cursor-pointer transition shadow-xs"
                            >
                                <span>Show</span>
                                <span id="show-count-val" class="font-bold underline decoration-neutral-300 underline-offset-2">100</span>
                                <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <div id="show-count-menu" class="hidden absolute right-0 mt-1 w-28 bg-white border border-neutral-200 rounded-xl shadow-lg py-1 z-30">
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-medium show-option" data-val="10">Show 10</button>
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-medium show-option" data-val="25">Show 25</button>
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-medium show-option" data-val="50">Show 50</button>
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-bold show-option text-blue-600" data-val="100">Show 100</button>
                            </div>
                        </div>

                        <!-- Filter by Kategori ⌵ Dropdown Button (Sesuai Page Dashboard (2).png) -->
                        <div class="relative" id="category-filter-wrapper">
                            <button 
                                type="button" 
                                id="btn-category-filter"
                                class="px-4 py-2.5 border border-neutral-300 rounded-xl text-xs sm:text-sm font-medium text-neutral-800 bg-white hover:bg-neutral-50 flex items-center gap-2 whitespace-nowrap cursor-pointer transition shadow-xs"
                            >
                                <span id="category-filter-label">Filter by Kategori</span>
                                <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <div id="category-filter-menu" class="hidden absolute right-0 mt-1 w-44 bg-white border border-neutral-200 rounded-xl shadow-lg py-1 z-30">
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-bold text-blue-600 category-option" data-cat="">Semua Kategori</button>
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-medium category-option" data-cat="lifestyle">Lifestyle</button>
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-medium category-option" data-cat="running">Running</button>
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-medium category-option" data-cat="sports">Sports</button>
                                <button type="button" class="w-full px-3 py-1.5 text-left text-xs hover:bg-neutral-100 font-medium category-option" data-cat="casual">Casual</button>
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Data Table -->
                    <div class="mt-5 overflow-x-auto">
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
                            <tbody class="divide-y divide-neutral-100" id="shoe-table-body">
                                @php
                                    $shoes = $shoeCatalog ?? [];
                                @endphp

                                @foreach ($shoes as $index => $item)
                                    <tr class="hover:bg-neutral-50/70 transition-colors shoe-row" data-id="{{ $item['id'] ?? $index }}" data-brand="{{ strtolower($item['brand'] ?? '') }}">
                                        <!-- Expand Arrow Button -->
                                        <td class="py-3.5 px-3 text-center align-middle">
                                            <button type="button" class="w-6 h-6 rounded-md border border-neutral-300 hover:bg-neutral-100 flex items-center justify-center text-neutral-600 transition cursor-pointer mx-auto">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                                </svg>
                                            </button>
                                        </td>

                                        <!-- Shoe Image & Name / Code -->
                                        <td class="py-3.5 px-4 align-middle">
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
                                                    <div class="text-[11px] text-neutral-400 font-normal shoe-code">
                                                        {{ $item['code'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Brand / Merek -->
                                        <td class="py-3.5 px-4 align-middle text-xs sm:text-sm text-neutral-800 shoe-brand font-medium">
                                            {{ $item['brand'] }}
                                        </td>

                                        <!-- Varian -->
                                        <td class="py-3.5 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-variant">
                                            {{ $item['variant'] }}
                                        </td>

                                        <!-- Unit Price -->
                                        <td class="py-3.5 px-4 align-middle text-xs sm:text-sm text-neutral-900 font-medium shoe-price">
                                            {{ $item['unit_price'] }}
                                        </td>

                                        <!-- Total Stock -->
                                        <td class="py-3.5 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-stock">
                                            {{ $item['total_stock'] }}
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-3.5 px-4 align-middle text-center shoe-status">
                                            @if ($item['status_type'] === 'overstock')
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A]">
                                                    {{ $item['status'] }}
                                                </span>
                                            @elseif ($item['status_type'] === 'habis' || $item['status_type'] === 'kritis')
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEE2E2] text-[#DC2626] border border-[#FECACA]">
                                                    {{ $item['status'] }}
                                                </span>
                                            @else
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-neutral-100 text-neutral-700 border border-neutral-200">
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
                        <div id="shoe-count-text">
                            Menampilkan <span class="font-semibold text-neutral-800">1-{{ count($shoes) }}</span> dari <span class="font-semibold text-neutral-800">{{ count($shoes) }}</span> produk
                        </div>
                        <div class="flex items-center gap-1 select-none font-medium text-neutral-700">
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100 text-neutral-500 cursor-pointer">&lsaquo;</button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded bg-neutral-900 text-white font-bold cursor-pointer">1</button>
                            <button type="button" class="w-6 h-6 flex items-center justify-center rounded hover:bg-neutral-100 cursor-pointer text-neutral-500">&rsaquo;</button>
                        </div>
                    </div>

                </section>

            </div>
        </main>

    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"></div>

    <!-- Client-side Interactive Search & Real-Time Engine -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const searchInput = document.getElementById('shoe-search');
            
            // Chart Date Range Popover & Interactivity
            const dateFilterBtn = document.getElementById('btn-chart-date-filter');
            const datePopover = document.getElementById('chart-date-popover');
            const dateChevron = document.getElementById('chart-date-chevron');
            const dateFilterLabel = document.getElementById('chart-date-filter-label');
            const applyDateBtn = document.getElementById('btn-apply-date-filter');
            const quickChips = document.querySelectorAll('.quick-chip');
            const segmentedBtns = document.querySelectorAll('.segmented-btn');
            const dateInputStart = document.getElementById('date-input-start');
            const dateInputEnd = document.getElementById('date-input-end');
            const dateSummaryText = document.getElementById('date-summary-text');

            let currentRange = '12m';
            let currentSegment = 'bulan';

            function toggleDatePopover() {
                const isHidden = datePopover.classList.contains('hidden');
                if (isHidden) {
                    datePopover.classList.remove('hidden');
                    if (dateChevron) dateChevron.style.transform = 'rotate(180deg)';
                } else {
                    datePopover.classList.add('hidden');
                    if (dateChevron) dateChevron.style.transform = 'rotate(0deg)';
                }
            }

            if (dateFilterBtn) {
                dateFilterBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDatePopover();
                });
            }

            // Quick Range Chips Click
            quickChips.forEach(chip => {
                chip.addEventListener('click', () => {
                    quickChips.forEach(c => {
                        c.className = 'quick-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-neutral-200 bg-white text-neutral-700 hover:bg-neutral-100 transition cursor-pointer';
                    });
                    chip.className = 'quick-chip px-3.5 py-1.5 rounded-full text-xs font-semibold bg-neutral-900 text-white border border-neutral-900 shadow-xs cursor-pointer active-chip';
                    currentRange = chip.getAttribute('data-range');

                    const text = chip.textContent.trim();
                    if (currentRange === '30d') {
                        dateInputStart.value = '21/10/2026';
                        dateInputEnd.value = '20/11/2026';
                        dateSummaryText.textContent = '30 hari - 30 batang per hari';
                    } else if (currentRange === '3m') {
                        dateInputStart.value = '01/08/2026';
                        dateInputEnd.value = '31/10/2026';
                        dateSummaryText.textContent = '92 hari - 3 batang per bulan';
                    } else if (currentRange === 'this_year') {
                        dateInputStart.value = '01/01/2026';
                        dateInputEnd.value = '31/10/2026';
                        dateSummaryText.textContent = '304 hari - 10 batang per bulan';
                    } else if (currentRange === '12m') {
                        dateInputStart.value = '01/11/2025';
                        dateInputEnd.value = '31/10/2026';
                        dateSummaryText.textContent = '304 hari - 10 batang per bulan';
                    } else if (currentRange === 'all') {
                        dateInputStart.value = '01/01/2024';
                        dateInputEnd.value = '31/10/2026';
                        dateSummaryText.textContent = '1034 hari - 34 batang';
                    }
                });
            });

            // Segmented Button Click (Hari / Minggu / Bulan)
            segmentedBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    segmentedBtns.forEach(b => {
                        b.className = 'flex-1 py-1.5 text-center text-neutral-600 rounded-lg hover:text-neutral-900 transition segmented-btn';
                    });
                    btn.className = 'flex-1 py-1.5 text-center text-neutral-900 font-bold bg-white rounded-lg shadow-xs transition segmented-btn active-segment';
                    currentSegment = btn.getAttribute('data-type');
                });
            });

            // Apply Button Click
            if (applyDateBtn) {
                applyDateBtn.addEventListener('click', () => {
                    if (currentRange === '30d') {
                        dateFilterLabel.textContent = '30 Hari Terakhir';
                    } else if (currentRange === '3m') {
                        dateFilterLabel.textContent = 'Agu 2026 - Okt 2026';
                    } else if (currentRange === 'this_year') {
                        dateFilterLabel.textContent = 'Jan 2026 - Okt 2026';
                    } else if (currentRange === '12m') {
                        dateFilterLabel.textContent = 'Nov 2025 - Okt 2026';
                    } else {
                        dateFilterLabel.textContent = `${dateInputStart.value} - ${dateInputEnd.value}`;
                    }
                    toggleDatePopover();
                    showToast('Rentang Tanggal Diterapkan', `Filter grafik disetel ke ${dateFilterLabel.textContent} (${currentSegment.toUpperCase()}).`);
                });
            }

            // Close popover when clicked outside
            document.addEventListener('click', (e) => {
                const wrapper = document.getElementById('chart-date-filter-wrapper');
                if (wrapper && !wrapper.contains(e.target) && datePopover && !datePopover.classList.contains('hidden')) {
                    datePopover.classList.add('hidden');
                    if (dateChevron) dateChevron.style.transform = 'rotate(0deg)';
                }
            });

            // Show Count Dropdown
            const btnShowCount = document.getElementById('btn-show-count');
            const showCountMenu = document.getElementById('show-count-menu');
            const showCountVal = document.getElementById('show-count-val');
            const showOptions = document.querySelectorAll('.show-option');

            if (btnShowCount && showCountMenu) {
                btnShowCount.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showCountMenu.classList.toggle('hidden');
                });

                showOptions.forEach(opt => {
                    opt.addEventListener('click', () => {
                        const val = opt.getAttribute('data-val');
                        showCountVal.textContent = val;
                        showCountMenu.classList.add('hidden');
                        applyTableFilter();
                    });
                });
            }

            // Category Filter Dropdown
            const btnCatFilter = document.getElementById('btn-category-filter');
            const catFilterMenu = document.getElementById('category-filter-menu');
            const catFilterLabel = document.getElementById('category-filter-label');
            const catOptions = document.querySelectorAll('.category-option');
            let selectedCategory = '';

            if (btnCatFilter && catFilterMenu) {
                btnCatFilter.addEventListener('click', (e) => {
                    e.stopPropagation();
                    catFilterMenu.classList.toggle('hidden');
                });

                catOptions.forEach(opt => {
                    opt.addEventListener('click', () => {
                        selectedCategory = opt.getAttribute('data-cat');
                        const labelText = opt.textContent.trim();
                        catFilterLabel.textContent = selectedCategory ? labelText : 'Filter by Kategori';
                        catFilterMenu.classList.add('hidden');
                        applyTableFilter();
                    });
                });
            }

            // Close menus when clicking outside
            document.addEventListener('click', (e) => {
                const showWrapper = document.getElementById('show-count-wrapper');
                if (showWrapper && !showWrapper.contains(e.target) && showCountMenu) {
                    showCountMenu.classList.add('hidden');
                }
                const catWrapper = document.getElementById('category-filter-wrapper');
                if (catWrapper && !catWrapper.contains(e.target) && catFilterMenu) {
                    catFilterMenu.classList.add('hidden');
                }
            });

            // Client-side Table Search & Filter
            function applyTableFilter() {
                const q = searchInput?.value.toLowerCase().trim() || '';
                const rows = document.querySelectorAll('.shoe-row');
                const maxShow = parseInt(showCountVal?.textContent || '100', 10);
                let visibleCount = 0;

                rows.forEach(row => {
                    const name = row.querySelector('.shoe-name')?.textContent.toLowerCase() || '';
                    const brand = row.querySelector('.shoe-brand')?.textContent.toLowerCase() || '';
                    const code = row.querySelector('.shoe-code')?.textContent.toLowerCase() || '';
                    
                    const matchQuery = !q || name.includes(q) || brand.includes(q) || code.includes(q);
                    const matchCategory = !selectedCategory || name.includes(selectedCategory) || brand.includes(selectedCategory);

                    if (matchQuery && matchCategory && visibleCount < maxShow) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const countEl = document.getElementById('shoe-count-text');
                if (countEl) {
                    countEl.innerHTML = `Menampilkan <span class="font-semibold text-neutral-800">${visibleCount > 0 ? '1-' + visibleCount : '0'}</span> dari <span class="font-semibold text-neutral-800">${rows.length}</span> produk`;
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyTableFilter);
            }

            // Floating Toast Notification
            function showToast(title, message, type = 'success') {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const toast = document.createElement('div');
                toast.className = `pointer-events-auto flex items-start gap-3 p-4 bg-white border ${type === 'success' ? 'border-emerald-200' : 'border-neutral-200'} rounded-2xl shadow-xl transition-all duration-300 transform translate-y-3 opacity-0`;
                
                const iconSvg = type === 'success' 
                    ? `<div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                       </div>`
                    : `<div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center flex-shrink-0">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                       </div>`;

                toast.innerHTML = `
                    ${iconSvg}
                    <div class="flex-1">
                        <div class="text-xs font-bold text-neutral-900">${title}</div>
                        <div class="text-[11px] text-neutral-500 mt-0.5 leading-snug">${message}</div>
                    </div>
                `;

                container.appendChild(toast);

                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-3', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                });

                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            }

            // Subtle Value Highlight Effect on Change
            function updateTextWithHighlight(el, newText) {
                if (!el) return;
                const oldText = el.textContent.trim();
                const trimmedNew = String(newText).trim();
                if (oldText !== trimmedNew) {
                    el.textContent = trimmedNew;
                    el.classList.add('value-highlight');
                    setTimeout(() => el.classList.remove('value-highlight'), 1200);
                }
            }

            // Update UI from Fresh JSON Metrics
            function renderDashboard(data) {
                if (!data) return;

                // Metric Cards
                if (data.stats) {
                    const statPenjualan = document.getElementById('stat-total-penjualan');
                    const statPersen = document.getElementById('stat-persen-penjualan');
                    const statTransaksi = document.getElementById('stat-total-transaksi');
                    const statTerjual = document.getElementById('stat-total-terjual');
                    const statPersenTerjual = document.getElementById('stat-persen-terjual');
                    const statStok = document.getElementById('stat-peringatan-stok');

                    if (statPenjualan) updateTextWithHighlight(statPenjualan, data.stats.total_penjualan_formatted || ('Rp' + Number(data.stats.total_penjualan).toLocaleString('id-ID')));
                    if (statPersen) statPersen.innerHTML = `Naik - ${data.stats.persen_penjualan.replace('+', '')}%`;
                    if (statTransaksi) updateTextWithHighlight(statTransaksi, `${data.stats.total_transaksi} Struk`);
                    if (statTerjual) updateTextWithHighlight(statTerjual, `${data.stats.total_terjual} Pasang`);
                    if (statPersenTerjual) statPersenTerjual.innerHTML = `Target bulanan tercapai - ↗${data.stats.persen_penjualan.replace('+', '')}%`;
                    if (statStok) updateTextWithHighlight(statStok, `${data.stats.peringatan_stok} Varian`);
                }

                // Monthly Sales Bar Chart
                if (Array.isArray(data.monthlySales)) {
                    const chartContainer = document.getElementById('monthly-sales-container');
                    if (chartContainer) {
                        chartContainer.innerHTML = data.monthlySales.map(bar => `
                            <div class="flex-1 flex flex-col items-center h-full justify-end group relative cursor-pointer">
                                <div class="absolute -top-8 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none bg-neutral-900 text-white text-[10px] font-semibold px-2 py-1 rounded shadow whitespace-nowrap z-10 chart-tooltip">
                                    ${bar.month}: ${bar.amount}
                                </div>
                                <div 
                                    class="w-full max-w-[28px] sm:max-w-[34px] bg-[#0052FF] group-hover:bg-[#003ecb] rounded-t-md transition-all duration-500 chart-bar"
                                    style="height: ${bar.height}%;"
                                ></div>
                                <span class="text-[11px] sm:text-xs font-semibold text-neutral-800 text-center mt-3 select-none chart-month">
                                    ${bar.month}
                                </span>
                            </div>
                        `).join('');
                    }

                    if (data.chartPills) {
                        const peakMonth = document.getElementById('chart-peak-month');
                        const peakAmount = document.getElementById('chart-peak-amount');
                        if (peakMonth) peakMonth.textContent = data.chartPills.peak_month || 'Okt';
                        if (peakAmount) peakAmount.textContent = data.chartPills.peak_amount || 'Puncak - Rp 435,4jt';
                    }
                }
            }

            // Fetch Real-Time Data from Server (Background sync)
            let isPolling = false;
            async function fetchRealtimeData() {
                if (isPolling) return;
                isPolling = true;

                try {
                    const response = await fetch("{{ route('dashboard.data') }}", {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        renderDashboard(data);
                    }
                } catch (err) {
                    console.warn('Real-time sync paused temporarily:', err);
                } finally {
                    isPolling = false;
                }
            }

            let pollInterval = setInterval(() => fetchRealtimeData(), 8000);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    if (pollInterval) clearInterval(pollInterval);
                } else {
                    fetchRealtimeData();
                    pollInterval = setInterval(() => fetchRealtimeData(), 8000);
                }
            });
        });
    </script>
</body>
</html>
