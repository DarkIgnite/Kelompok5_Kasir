<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-screen w-screen overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Styles & Scripts with Tailwind CDN Fallback -->
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
        html, body {
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body class="h-screen w-screen overflow-hidden bg-white text-gray-900 antialiased selection:bg-neutral-900 selection:text-white">
    <div class="h-screen w-full flex flex-col lg:flex-row overflow-hidden">
        
        <!-- Left Side: Hero Image Banner (Desktop & Tablet) -->
        <div class="hidden lg:relative lg:flex lg:w-1/2 h-full overflow-hidden select-none" style="background-color: #1b1b1b;">
            <img 
                src="{{ asset('images/auth-banner.png') }}" 
                alt="KasirAja - Move in Silence." 
                class="w-5/6 h-5/6 object-contain object-left pointer-events-none"
                loading="eager"
            />
        </div>

        <!-- Right Side: Login Form Area (Centered vertically & horizontally) -->
        <div class="w-full lg:w-1/2 h-full flex flex-col justify-center items-center px-6 sm:px-12 lg:px-8 xl:px-16 bg-white overflow-y-auto">
            
            <!-- Mobile Brand Header (Visible on smaller screens where banner is hidden) -->
            <!-- <div class="lg:hidden flex items-center gap-2.5 mb-6 self-start sm:self-center">
                <div class="w-9 h-9 bg-black rounded-xl flex items-center justify-center p-1.5 shadow-sm">
                    <img src="{{ asset('images/logo-black.png') }}" alt="KasirAja" class="w-full h-full object-contain">
                </div>
                <span class="text-lg font-bold tracking-tight text-gray-900">KasirAja</span>
            </div> -->

            <!-- Form Container (max width 380px) -->
            <div class="w-full max-w-[380px] mx-auto flex flex-col items-center">
                
                <!-- Center Square Logo Icon -->
                <div class="mb-4 sm:mb-5 flex justify-center">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-black flex items-center justify-center p-1.5 shadow-sm">
                        <img 
                            src="{{ asset('images/logo-black.png') }}" 
                            alt="KasirAja Logo" 
                            class="w-full h-full object-contain"
                        />
                    </div>
                </div>

                <!-- Page Heading & Subheading -->
                <h1 class="text-xl sm:text-2xl font-extrabold text-neutral-900 text-center tracking-tight leading-tight">
                    Selamat Datang di Sistem KasirAja
                </h1>
                <p class="text-xs sm:text-sm text-neutral-500 text-center mt-1.5 font-normal">
                    Silahkan Masuk dengan akun Anda
                </p>

                <!-- Feedback / Status / Error Alert -->
                @if (session('status'))
                    <div class="w-full mt-3.5 px-3.5 py-2.5 bg-neutral-100 border border-neutral-300 text-neutral-800 text-xs rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 text-neutral-600    flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="w-full mt-3.5 px-3.5 py-2.5 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('login') }}" method="POST" class="w-full mt-6 space-y-4">
                    @csrf

                    <!-- Username Field -->
                    <div class="space-y-1.5">
                        <label for="username" class="block text-xs font-bold text-neutral-900 tracking-wider uppercase">
                            USERNAME
                        </label>
                        <div class="relative flex items-center">
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                value="{{ old('username') }}"
                                placeholder="Username" 
                                autocomplete="username"
                                required
                                class="w-full px-4 py-2.5 sm:py-3 pr-11 text-xs sm:text-sm bg-white border border-neutral-300 rounded-xl text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-500 shadow-none"
                            />
                            <div class="absolute right-3.5 pointer-events-none text-neutral-700">
                                <!-- Person / User Outline Icon -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-neutral-900 tracking-wider uppercase">
                            PASSWORD
                        </label>
                        <div class="relative flex items-center">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Password" 
                                autocomplete="current-password"
                                required
                                class="w-full px-4 py-2.5 sm:py-3 pr-11 text-xs sm:text-sm bg-white border border-neutral-300 rounded-xl text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-neutral-500 shadow-none"
                            />
                            <!-- Password Toggle Eye Icon -->
                            <button 
                                type="button" 
                                id="toggle-password-btn" 
                                onclick="togglePasswordVisibility()"
                                class="absolute right-3.5 p-1 text-neutral-700 hover:text-black focus:outline-none cursor-pointer"
                                aria-label="Toggle password visibility"
                            >
                                <!-- Eye Icon (Visible when masked) -->
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <!-- Eye-slash Icon (Visible when plain text) -->
                                <svg id="eye-slash-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>
                    </div>


                    <!-- Submit Login Button -->
                    <button 
                        type="submit" 
                        class="w-full !mt-5 bg-black hover:bg-neutral-800 active:scale-[0.99] text-white font-semibold py-2.5 sm:py-3 px-4 rounded-xl text-xs sm:text-sm tracking-wide transition-all duration-150 shadow-sm cursor-pointer"
                    >
                        Login
                    </button>

                    <!-- Footer Link -->
                    <p class="text-center text-xs text-neutral-500 !mt-4 sm:!mt-5">
                        Butuh akun kasir? &nbsp;&nbsp;&nbsp; Hubungi admin toko
                    </p>
                </form>

            </div>
        </div>

    </div>

    <!-- Password Visibility Toggle Script -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeSlashIcon.classList.add('hidden');
                eyeIcon.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>
