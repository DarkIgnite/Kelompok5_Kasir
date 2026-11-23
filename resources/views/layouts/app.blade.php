<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'KasirAja') - KasirAja</title>
    <!-- Logo KasirAja di tab browser -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts: Inter (sama dengan desain Figma) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #FFFFFF;
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
            class="min-h-screen flex-1 bg-white transition-all"
            style="margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width));"
        >
            <div class="w-full max-w-[1600px] mx-auto px-[30px] pt-[25px] pb-[25px] space-y-[15px]">

                <!-- TOP HEADER BAR (ukuran mengikuti Figma: tinggi 56, judul Inter Bold 32) -->
                <header class="flex items-center justify-between gap-[10px] min-h-[56px]">
                    <div class="min-w-0">
                        <h1 class="text-[32px] leading-[39px] font-bold text-black">
                            @yield('page_title', 'Halaman')
                        </h1>
                        @if (trim($__env->yieldContent('page_subtitle')))
                            <p class="text-sm text-neutral-500 font-normal mt-1">@yield('page_subtitle')</p>
                        @endif
                    </div>

                    <!-- User Badge -->
                    @auth
                        @php $isAdminUser = Auth::user()->role === 'admin'; @endphp
                        <div class="shrink-0 flex items-center gap-[10px] h-[56px] px-[10px] py-[6px] bg-[#F2F2F2] rounded-[10px] border border-black/30">
                            <div class="w-[42px] h-[42px] rounded-[8px] bg-black text-white flex items-center justify-center text-[18px] font-bold uppercase">
                                {{ substr(Auth::user()->nama_lengkap ?? Auth::user()->username ?? 'A', 0, 1) }}
                            </div>
                            <div class="text-left">
                                <div class="text-[14px] leading-[17px] font-bold text-black">{{ Auth::user()->nama_lengkap ?? Auth::user()->username }}</div>
                                <span class="mt-[6px] inline-flex items-center h-[19px] px-[7px] rounded-[4px] border text-[11px] font-bold uppercase {{ $isAdminUser ? 'bg-[#F3E8FF] text-[#7E22CE] border-[#7E22CE]/35' : 'bg-blue-50 text-blue-700 border-blue-700/35' }}">
                                    {{ strtoupper(Auth::user()->role ?? 'admin') }}
                                </span>
                            </div>
                        </div>
                    @endauth
                </header>

                <!-- Page Content -->
                @yield('content')

            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
