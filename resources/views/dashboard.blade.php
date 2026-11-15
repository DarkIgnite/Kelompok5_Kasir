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

                <!-- 1. TOP HEADER BAR -->
                <header class="flex flex-col lg:flex-row lg:items-center justify-between pb-6 border-b border-neutral-200/90 gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-xl sm:text-2xl lg:text-[26px] font-extrabold text-neutral-900 tracking-tight leading-snug">
                                Dashboard Penjualan & Inventaris
                            </h1>
                       <</div>
                      <p class="text-xs sm:text-sm text-neutral-500 font-normal mt-1 flex items-center gap-2">
                            <span>Ringkasan performa penjualan harian, stok gudang & transaksi kasir real-time dari Toko.</span>
                            <span class="text-neutral-300 hidden md:inline">•</span>
                             <span id="header-live-clock" class="text-neutral-600 font-medium hidden md:inline"></span> 
                        </p>
                       </div>

                    <!-- Right Contro   ls: Real-time Indicator, User Badge, Buttons -->
                    <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                        
                        <!-- Real-time Live Status Pill 
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200/90 rounded-xl text-xs font-semibold text-emerald-800 shadow-xs" title="Data diperbarui otomatis secara real-time setiap 5 detik">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            <span id="realtime-status-label" class="tracking-tight font-bold">Real-Time</span>
                            <span class="text-emerald-300">•</span>
                            <span id="realtime-clock" class="font-mono text-[11px] text-emerald-700 font-semibold">{{ $timestamp ?? now()->format('H:i:s') }}</span>
                        </div>

                        <!-- Manual Refresh Button 
                        <button 
                            id="btn-refresh-data" 
                            type="button" 
                            class="p-2 border border-neutral-300 rounded-xl text-neutral-600 hover:text-neutral-900 bg-white hover:bg-neutral-50 shadow-xs transition active:scale-95 cursor-pointer"
                            title="Segarkan data sekarang (Manual Refresh)"
                        >
                            <svg id="refresh-icon" class="w-4 h-4 transition-transform duration-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>
                        </button> -->

                        <!-- Simulasi Transaksi Baru (Uji Real-time Kasir) 
                        <button 
                            id="btn-simulate-sale" 
                            type="button" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-neutral-900 hover:bg-neutral-800 text-white rounded-xl text-xs font-bold shadow-sm transition active:scale-95 cursor-pointer"
                            title="Simulasikan transaksi baru dari kasir untuk melihat angka bertambah secara real-time"
                        >
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"/>
                            </svg>
                            <span>Simulasi Kasir</span>
                        </button> -->

                        @auth
                            <div class="flex items-center gap-2.5 px-3 py-1.5 bg-neutral-100 rounded-xl border border-neutral-200">
                                <div class="w-8 h-8 rounded-lg bg-black text-white flex items-center justify-center text-xs font-bold uppercase">
                                    {{ substr(Auth::user()->nama_lengkap ?? Auth::user()->username, 0, 1) }}
                                </div>
                                <div class="text-left hidden sm:block">
                                    <div class="text-xs font-bold text-neutral-900 leading-tight">{{ Auth::user()->nama_lengkap ?? Auth::user()->username }}</div>
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold uppercase tracking-wider {{ Auth::user()->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ Auth::user()->role }}
                                    </span>
                                </div>
                            </div>
                        @endauth

                        <!-- Period Dropdown -->
                        <div class="relative inline-block text-left">
                            <button 
                                type="button" 
                                class="inline-flex items-center gap-2.5 px-3.5 py-2 border border-neutral-300 rounded-xl text-xs sm:text-sm font-semibold text-neutral-800 bg-white hover:bg-neutral-50 transition cursor-pointer"
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
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="2" y="6" width="20" height="12" rx="2"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                        <path d="M6 12h.01M18 12h.01"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-total-penjualan" class="mt-2 text-xl sm:text-2xl font-black text-neutral-900 tracking-tight transition-all duration-300">
                                {{ $stats['total_penjualan_formatted'] ?? ('Rp' . number_format($stats['total_penjualan'] ?? 0, 0, ',', '.')) }}
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span id="stat-persen-penjualan" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#E6F9F0] text-[#00A854]">
                                ↗ {{ $stats['persen_penjualan'] ?? '+0' }}%
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
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-total-transaksi" class="mt-2 text-xl sm:text-2xl font-black text-neutral-900 tracking-tight transition-all duration-300">
                                {{ $stats['total_transaksi'] ?? 0 }} Struk
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span id="stat-rata-rata-nota" class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-600">
                                {{ $stats['rata_rata_nota_formatted'] ?? ('Rp' . number_format($stats['rata_rata_nota'] ?? 0, 0, ',', '.')) }}
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
                                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-total-terjual" class="mt-2 text-xl sm:text-2xl font-black text-neutral-900 tracking-tight transition-all duration-300">
                                {{ $stats['total_terjual'] ?? 0 }} Pasang
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span id="stat-persen-terjual" class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#E6F9F0] text-[#00A854]">
                                ↗ {{ $stats['persen_penjualan'] ?? '+0' }}%
                            </span>
                            <span class="text-[11px] text-neutral-400 font-medium ml-2">
                                Target bulanan aktif
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
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="stat-peringatan-stok" class="mt-2 text-xl sm:text-2xl font-black text-red-600 tracking-tight transition-all duration-300">
                                {{ $stats['peringatan_stok'] ?? 0 }} Varian
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-end">
                            <a href="#katalog-stok" class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 transition flex items-center gap-1">
                                Lihat detail →
                            </a>
                        </div>
                    </div>

                </section>

                <!-- 3. DASHBOARD CHARTS AND INVENTORY PANELS -->
                <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                    
                    <!-- Left: Tren Penjualan Bulanan (8 cols on XL, 7 cols on LG) -->
                    <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-2xl border border-neutral-200/90 p-5 sm:p-6 flex flex-col justify-between shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-bold text-neutral-900 tracking-tight">
                                    Tren Penjualan Bulanan
                                </h2>
                                <p class="text-xs text-neutral-500 mt-0.5">
                                    Perbandingan volume penjualan per-bulan dari transaksi kasir.
                                </p>
                            </div>
                            <span class="text-[11px] text-neutral-400 font-medium hidden sm:inline">12 Bulan Terakhir</span>
                        </div>

                        <!-- Bar Chart Graphic -->
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

                        <!-- 3 Summary Pills at bottom of chart card -->
                        <div class="grid grid-cols-3 gap-3 sm:gap-4 mt-6 pt-5" style="padding-top: 0px">
                            <!-- Box 1 -->
                            <div class="border border-neutral-400/80 rounded-xl p-3 sm:p-4 text-center">
                                <div id="chart-peak-month" class="text-sm sm:text-base font-extrabold text-neutral-900">
                                    {{ $chartPills['peak_month'] ?? 'Okt' }}
                                </div>
                                <div id="chart-peak-amount" class="text-[11px] sm:text-xs text-neutral-500 mt-0.5">
                                    {{ $chartPills['peak_amount'] ?? 'Puncak - Rp 0' }}
                                </div>
                            </div>
                            <!-- Box 2 -->
                            <div class="border border-neutral-400/80 rounded-xl p-3 sm:p-4 text-center">
                                <div id="chart-avg-amount" class="text-sm sm:text-base font-extrabold text-neutral-900">
                                    {{ $chartPills['avg_amount'] ?? 'Rp 0' }}
                                </div>
                                <div class="text-[11px] sm:text-xs text-neutral-500 mt-0.5">
                                    Rata-rata/bln
                                </div>
                            </div>
                            <!-- Box 3 -->
                            <div class="border border-neutral-400/80 rounded-xl p-3 sm:p-4 text-center">
                                <div id="chart-growth-rate" class="text-sm sm:text-base font-extrabold text-neutral-900">
                                    {{ $chartPills['growth_rate'] ?? '+0%' }}
                                </div>
                                <div class="text-[11px] sm:text-xs text-neutral-500 mt-0.5">
                                    Proyeksi Real-time
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Inventory alerts aligned with the sales chart -->
                    <div class="lg:col-span-5 xl:col-span-4 flex flex-col gap-5 lg:h-full">
                    <!-- Card: Perlu Restock Segera -->
                    <div class="bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm lg:flex-[2]">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 flex-shrink-0 animate-pulse"></span>
                                <h2 class="text-sm font-bold text-neutral-900 tracking-tight">
                                    Perlu Restock Segera
                                </h2>
                            </div>

                            <div id="restock-alerts-container" class="divide-y divide-black/10">
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
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $alert['status'] === 'Habis' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }} whitespace-nowrap">
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
                    </div>

                    <!-- Card: Stok Menumpuk / Slow-Moving -->
                    <div class="bg-white rounded-2xl border border-neutral-200/90 p-5 shadow-sm flex flex-col justify-start lg:flex-1">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0"></span>
                            <h2 class="text-sm font-bold text-neutral-900 tracking-tight">
                                Stok Menumpuk / Slow-Moving
                            </h2>
                        </div>

                        <div id="slow-moving-container" class="divide-y divide-black/10">
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
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">
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

                <!-- Card: Katalog Stok Sepatu -->
                <section id="katalog-stok" class="w-full bg-white rounded-2xl border border-neutral-200/90 p-5 sm:p-6 shadow-sm">
                    
                    <!-- Table Title & Subtitle -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-neutral-900 tracking-tight">
                                Katalog Stok Sepatu
                            </h2>
                            <p class="text-xs text-neutral-500 mt-0.5">
                                Data real-time fisik gudang dan harga jual retail kasir.
                            </p>
                        </div>
                        <div class="text-xs text-neutral-500 font-medium">
                            Status data: <span class="text-emerald-600 font-bold">Sinkron Live</span>
                        </div>
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
                                placeholder="Cari nama sepatu, kode, atau brand..." 
                                class="w-full pl-10 pr-4 py-2 bg-white border border-neutral-200 rounded-xl text-xs sm:text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-400 transition"
                            />
                        </div>

                        <!-- Dropdown Filter -->
                        <div class="relative">
                            <button 
                                type="button" 
                                class="w-full sm:w-auto px-4 py-2 border border-neutral-200 rounded-xl text-xs sm:text-sm font-medium text-neutral-800 bg-white flex items-center justify-between sm:justify-center gap-2 whitespace-nowrap cursor-pointer hover:bg-neutral-50 transition"
                            >
                                <span>Semua Status</span>
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
                                    <th class="py-3 px-3 w-10 text-center">#</th>
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
                                    $shoes = $shoeCatalog ?? [];
                                @endphp

                                @foreach ($shoes as $index => $item)
                                    <tr class="hover:bg-neutral-50/70 transition-colors shoe-row" data-id="{{ $item['id'] ?? $index }}">
                                        <!-- Index Number -->
                                        <td class="py-3 px-3 text-center align-middle text-neutral-400 font-mono text-xs">
                                            {{ $index + 1 }}
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
                                                    <div class="text-[11px] text-neutral-400 font-normal shoe-code">
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
                                        <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-variant">
                                            {{ $item['variant'] }}
                                        </td>

                                        <!-- Unit Price -->
                                        <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-price">
                                            {{ $item['unit_price'] }}
                                        </td>

                                        <!-- Total Stock -->
                                        <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-stock">
                                            {{ $item['total_stock'] }}
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-3 px-4 align-middle text-center shoe-status">
                                            @if ($item['status_type'] === 'overstock')
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A]">
                                                    {{ $item['status'] }}
                                                </span>
                                            @elseif ($item['status_type'] === 'habis')
                                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEE2E2] text-[#DC2626] border border-[#FECACA]">
                                                    {{ $item['status'] }}
                                                </span>
                                            @elseif ($item['status_type'] === 'kritis')
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
            const refreshBtn = document.getElementById('btn-refresh-data');
            const refreshIcon = document.getElementById('refresh-icon');
            const simulateBtn = document.getElementById('btn-simulate-sale');
            const clockEl = document.getElementById('realtime-clock');
            const headerClockEl = document.getElementById('header-live-clock');
            const statusLabel = document.getElementById('realtime-status-label');

            let isPolling = false;
            let pollInterval = null;
            let lastDataHash = '';

            // 1. Live Digital Clock (HH:mm:ss WIB)
            function updateClock() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
                if (clockEl) clockEl.textContent = timeString;
                if (headerClockEl) {
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    const dayName = days[now.getDay()];
                    const dateNum = now.getDate();
                    const monthName = months[now.getMonth()];
                    const yearNum = now.getFullYear();
                    headerClockEl.textContent = `${dayName}, ${dateNum} ${monthName} ${yearNum} • ${timeString} WIB`;
                }
            }
            setInterval(updateClock, 1000);
            updateClock();

            // 2. Client-side Table Search Filter
            function applyTableFilter() {
                const q = searchInput?.value.toLowerCase().trim() || '';
                const rows = document.querySelectorAll('.shoe-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const name = row.querySelector('.shoe-name')?.textContent.toLowerCase() || '';
                    const brand = row.querySelector('.shoe-brand')?.textContent.toLowerCase() || '';
                    const code = row.querySelector('.shoe-code')?.textContent.toLowerCase() || '';
                    
                    if (name.includes(q) || brand.includes(q) || code.includes(q)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const countEl = document.getElementById('shoe-count-text');
                if (countEl) {
                    countEl.innerHTML = `Menampilkan <span class="font-semibold text-neutral-800">${visibleCount}</span> dari <span class="font-semibold text-neutral-800">${rows.length}</span> produk`;
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyTableFilter);
            }

            // 3. Floating Toast Notification
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

                // Animate in
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-3', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                });

                // Auto remove after 4.5 seconds
                setTimeout(() => {
                    toast.classList.add('opacity-0', 'translate-y-2');
                    setTimeout(() => toast.remove(), 300);
                }, 4500);
            }

            // 4. Subtle Value Highlight Effect on Change
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

            // 5. Update UI from Fresh JSON Metrics
            function renderDashboard(data) {
                if (!data) return;

                // A. 4 Metric Cards
                if (data.stats) {
                    const statPenjualan = document.getElementById('stat-total-penjualan');
                    const statPersen = document.getElementById('stat-persen-penjualan');
                    const statTransaksi = document.getElementById('stat-total-transaksi');
                    const statRata = document.getElementById('stat-rata-rata-nota');
                    const statTerjual = document.getElementById('stat-total-terjual');
                    const statPersenTerjual = document.getElementById('stat-persen-terjual');
                    const statStok = document.getElementById('stat-peringatan-stok');

                    if (statPenjualan) updateTextWithHighlight(statPenjualan, data.stats.total_penjualan_formatted || ('Rp' + Number(data.stats.total_penjualan).toLocaleString('id-ID')));
                    if (statPersen) statPersen.innerHTML = `↗ ${data.stats.persen_penjualan}%`;
                    if (statTransaksi) updateTextWithHighlight(statTransaksi, `${data.stats.total_transaksi} Struk`);
                    if (statRata) updateTextWithHighlight(statRata, data.stats.rata_rata_nota_formatted || ('Rp' + Number(data.stats.rata_rata_nota).toLocaleString('id-ID')));
                    if (statTerjual) updateTextWithHighlight(statTerjual, `${data.stats.total_terjual} Pasang`);
                    if (statPersenTerjual) statPersenTerjual.innerHTML = `↗ ${data.stats.persen_penjualan}%`;
                    if (statStok) updateTextWithHighlight(statStok, `${data.stats.peringatan_stok} Varian`);
                }

                // B. Monthly Sales Bar Chart
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
                        const avgAmount = document.getElementById('chart-avg-amount');
                        const growthRate = document.getElementById('chart-growth-rate');

                        if (peakMonth) peakMonth.textContent = data.chartPills.peak_month || 'Okt';
                        if (peakAmount) peakAmount.textContent = data.chartPills.peak_amount || 'Puncak - Rp 0';
                        if (avgAmount) avgAmount.textContent = data.chartPills.avg_amount || 'Rp 0';
                        if (growthRate) growthRate.textContent = data.chartPills.growth_rate || '+0%';
                    }
                }

                // C. Top Categories
                if (Array.isArray(data.topCategories)) {
                    const catContainer = document.getElementById('top-categories-container');
                    if (catContainer) {
                        catContainer.innerHTML = data.topCategories.map(cat => `
                            <div>
                                <div class="flex items-center justify-between text-xs font-medium text-neutral-900 mb-1.5">
                                    <span>${cat.name}</span>
                                    <span class="font-bold text-neutral-800">(${cat.count} PSG) - ${cat.percent}%</span>
                                </div>
                                <div class="w-full bg-neutral-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-[#0052FF] h-full rounded-full transition-all duration-500" style="width: ${cat.percent}%;"></div>
                                </div>
                            </div>
                        `).join('');
                    }
                }

                // D. Restock Alerts
                if (Array.isArray(data.restockAlerts)) {
                    const alertContainer = document.getElementById('restock-alerts-container');
                    if (alertContainer) {
                        if (data.restockAlerts.length === 0) {
                            alertContainer.innerHTML = `<div class="py-4 text-center text-xs text-neutral-400">Semua stok aman dan mencukupi.</div>`;
                        } else {
                            alertContainer.innerHTML = data.restockAlerts.map(alert => `
                                <div class="py-3 first:pt-1 last:pb-1 flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900">
                                            ${alert.name}
                                        </div>
                                        <div class="text-[11px] text-neutral-500 mt-0.5">
                                            ${alert.detail}
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${alert.status === 'Habis' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200'} whitespace-nowrap">
                                        ${alert.status}
                                    </span>
                                </div>
                            `).join('');
                        }
                    }
                }

                // E. Category Margins
                if (Array.isArray(data.categoryMargins)) {
                    const marginContainer = document.getElementById('category-margins-container');
                    if (marginContainer) {
                        marginContainer.innerHTML = data.categoryMargins.map(m => `
                            <div class="py-2.5 first:pt-1 last:pb-1 flex items-center justify-between text-xs sm:text-sm">
                                <span class="text-neutral-800 font-medium">${m.category}</span>
                                <span class="font-bold ${m.is_highlight ? 'text-emerald-600' : 'text-neutral-900'}">
                                    ${m.margin}
                                </span>
                            </div>
                        `).join('');
                    }
                }

                // F. Slow-Moving Items
                if (Array.isArray(data.slowMoving)) {
                    const slowContainer = document.getElementById('slow-moving-container');
                    if (slowContainer) {
                        if (data.slowMoving.length === 0) {
                            slowContainer.innerHTML = `<div class="py-4 text-center text-xs text-neutral-400">Tidak ada stok menumpuk saat ini.</div>`;
                        } else {
                            slowContainer.innerHTML = data.slowMoving.map(item => `
                                <div class="py-3 first:pt-1 last:pb-1 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-bold text-neutral-900">
                                            ${item.name}
                                        </div>
                                        <div class="text-[11px] text-neutral-500 mt-0.5">
                                            ${item.detail}
                                        </div>
                                        ${item.note ? `<div class="text-[11px] text-neutral-500 mt-1">${item.note}</div>` : ''}
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 whitespace-nowrap">
                                        ${item.status || 'Overstock'}
                                    </span>
                                </div>
                            `).join('');
                        }
                    }
                }

                // G. Shoe Catalog Table
                if (Array.isArray(data.shoeCatalog)) {
                    const tableBody = document.getElementById('shoe-table-body');
                    if (tableBody) {
                        tableBody.innerHTML = data.shoeCatalog.map((item, index) => {
                            let badgeHtml = '';
                            if (item.status_type === 'overstock') {
                                badgeHtml = `<span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEF3C7] text-[#D97706] border border-[#FDE68A]">${item.status}</span>`;
                            } else if (item.status_type === 'habis' || item.status_type === 'kritis') {
                                badgeHtml = `<span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-[#FEE2E2] text-[#DC2626] border border-[#FECACA]">${item.status}</span>`;
                            } else {
                                badgeHtml = `<span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold bg-neutral-100 text-neutral-700 border border-neutral-200">${item.status}</span>`;
                            }

                            return `
                                <tr class="hover:bg-neutral-50/70 transition-colors shoe-row" data-id="${item.id || index}">
                                    <td class="py-3 px-3 text-center align-middle text-neutral-400 font-mono text-xs">
                                        ${index + 1}
                                    </td>
                                    <td class="py-3 px-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-neutral-50 border border-neutral-100 flex items-center justify-center p-1 flex-shrink-0 overflow-hidden shadow-xs">
                                                <img 
                                                    src="/${item.image}" 
                                                    alt="${item.name}" 
                                                    class="w-full h-full object-contain"
                                                    loading="lazy"
                                                />
                                            </div>
                                            <div>
                                                <div class="font-bold text-xs sm:text-sm text-neutral-900 shoe-name">
                                                    ${item.name}
                                                </div>
                                                <div class="text-[11px] text-neutral-400 font-normal shoe-code">
                                                    ${item.code}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 shoe-brand font-medium">
                                        ${item.brand}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-variant">
                                        ${item.variant}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-price">
                                        ${item.unit_price}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-xs sm:text-sm text-neutral-800 font-medium shoe-stock">
                                        ${item.total_stock}
                                    </td>
                                    <td class="py-3 px-4 align-middle text-center shoe-status">
                                        ${badgeHtml}
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        // Re-apply search filter after table update
                        applyTableFilter();
                    }
                }
            }

            // 6. Fetch Real-Time Data from Server
            async function fetchRealtimeData(isManual = false) {
                if (isPolling) return;
                isPolling = true;

                if (isManual && refreshIcon) {
                    refreshIcon.classList.add('animate-spin');
                }

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
                        if (isManual) {
                            showToast('Data Disinkronkan', 'Data dashboard telah diperbarui ke status paling terkini.');
                        }
                    }
                } catch (err) {
                    console.warn('Real-time polling paused temporarily:', err);
                } finally {
                    isPolling = false;
                    if (isManual && refreshIcon) {
                        setTimeout(() => refreshIcon.classList.remove('animate-spin'), 600);
                    }
                }
            }

            // Manual Refresh Click Listener
            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => fetchRealtimeData(true));
            }

            // 7. Simulate Sale Transaction (POST /dashboard/simulate-sale)
            if (simulateBtn) {
                simulateBtn.addEventListener('click', async () => {
                    simulateBtn.disabled = true;
                    simulateBtn.classList.add('opacity-75');

                    try {
                        const response = await fetch("{{ route('dashboard.simulate') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            renderDashboard(result.data);
                            const sale = result.sale;
                            showToast(
                                'Transaksi Kasir Baru!', 
                                `${sale.produk} (${sale.ukuran}/${sale.warna}) terjual seharga ${sale.total}. Stok & penjualan otomatis terupdate!`,
                                'success'
                            );
                        } else {
                            showToast('Gagal Simulasi', result.message || 'Tidak dapat membuat transaksi simulasi.', 'error');
                        }
                    } catch (err) {
                        showToast('Koneksi Error', 'Gagal menghubungi server.', 'error');
                    } finally {
                        simulateBtn.disabled = false;
                        simulateBtn.classList.remove('opacity-75');
                    }
                });
            }

            // 8. Start Background Polling Interval (every 5 seconds)
            function startPolling() {
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = setInterval(() => fetchRealtimeData(false), 5000);
            }

            function stopPolling() {
                if (pollInterval) clearInterval(pollInterval);
                pollInterval = null;
            }

            // Handle browser tab visibility (slow down when inactive, resume when active)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopPolling();
                } else {
                    fetchRealtimeData(false);
                    startPolling();
                }
            });

            // Start polling initially
            startPolling();
        });
    </script>
</body>
</html>