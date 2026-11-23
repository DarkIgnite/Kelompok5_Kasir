<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#FBFBFB]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'KasirAja') - KasirAja</title>
    <!-- Logo KasirAja di tab browser -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

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
    </style>
    @stack('styles')
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

                <!-- TOP HEADER BAR -->
                <header class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 gap-4">
                    <div>
                        <h1 class="text-2xl lg:text-[28px] font-black text-neutral-900 tracking-tight leading-snug">
                            @yield('page_title', 'Halaman')
                        </h1>
                        @if (trim($__env->yieldContent('page_subtitle')))
                            <p class="text-xs sm:text-sm text-neutral-500 font-normal mt-1 flex items-center gap-2">
                                <span>@yield('page_subtitle')</span>
                            </p>
                        @endif
                    </div>

                    <!-- Right Controls: User Badge -->
                    <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
                        @auth
                            <div class="flex items-center gap-3 px-3.5 py-1.5 bg-white rounded-xl border border-neutral-200 shadow-2xs">
                                <div class="w-8 h-8 rounded-lg bg-black text-white flex items-center justify-center text-sm font-bold uppercase">
                                    {{ substr(Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'A', 0, 1) }}
                                </div>
                                <div class="text-left hidden sm:block">
                                    <div class="text-xs font-bold text-neutral-900 leading-tight">{{ Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'Administrator Toko' }}</div>
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ Auth::user()->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ strtoupper(Auth::user()->role ?? 'admin') }}
                                    </span>
                                </div>
                            </div>
                        @endauth
                    </div>
                </header>

                <!-- Page Content -->
                @yield('content')

            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
