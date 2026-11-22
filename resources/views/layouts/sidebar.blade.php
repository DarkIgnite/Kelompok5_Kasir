<!-- SIDEBAR (Fixed, Height 100vh, matching design in Page Dashboard) -->
<aside 
    class="fixed top-0 left-0 bottom-0 z-50 bg-black flex flex-col items-center py-6 transition-all select-none shadow-2xl overflow-y-auto overflow-x-hidden [&::-webkit-scrollbar]:hidden [-ms-overflow-style:none] [scrollbar-width:none]"
    style="width: var(--sidebar-width, 76px); height: 100vh;"
    aria-label="Sidebar Menu"
>
    <!-- 1. Top Logo KasirAja (White Square with "K") -->
    <a 
        href="{{ route('dashboard') }}" 
        class="w-11 h-11 bg-white flex items-center justify-center p-1 cursor-pointer transition-transform duration-200 hover:scale-105 shadow-sm group" 
        title="KasirAja"
    >
        <img src="{{ asset('images/logo-white.svg') }}" alt="Logo KasirAja" class="w-7 h-7">
    </a>

    <!-- Subtle divider line -->
    <div class="w-full h-[1px] bg-neutral-900/90 my-4"></div>

    <!-- Navigation Menu Items -->
    <nav class="flex flex-col items-center w-full">
        <!-- 1. Dashboard -->
        @php
            $isDashboard = request()->routeIs('dashboard*');
        @endphp
        <div class="w-full flex justify-center py-1">
            <a 
                href="{{ route('dashboard') }}" 
                class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 relative group {{ $isDashboard ? 'text-white' : 'text-neutral-500 hover:text-white' }}"
                title="Dashboard"
            >
                <!-- Rounded 3-block grid icon (1 tall left, 2 stacked right) -->
                <svg class="w-7 h-7 transition-colors duration-200" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3.5" y="3.5" width="8" height="21" rx="2.5"></rect>
                    <rect x="15" y="3.5" width="9.5" height="9" rx="2"></rect>
                    <rect x="15" y="15.5" width="9.5" height="9" rx="2"></rect>
                </svg>

                <!-- Tooltip hover -->
                <span class="absolute left-full ml-3 px-2.5 py-1 bg-neutral-900 text-white text-xs font-semibold rounded-md opacity-0 pointer-events-none group-hover:opacity-100 transition whitespace-nowrap z-50 shadow-xl border border-neutral-800">
                    Dashboard
                </span>
            </a>
        </div>

        <!-- Subtle divider line -->
        <div class="w-full h-[1px] bg-neutral-900/90 my-2"></div>

        <!-- 2. Kasir / Transaksi -->
        @php
            $isKasir = request()->routeIs('kasir*') || request()->routeIs('transaksi*');
        @endphp
        <div class="w-full flex justify-center py-1">
            <a 
                href="{{ route('kasir.index') }}" 
                class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 relative group {{ $isKasir ? 'text-white' : 'text-neutral-500 hover:text-white' }}"
                title="Kasir / Transaksi"
            >
                <!-- Cash Register Icon -->
                <svg class="w-7 h-7 transition-colors duration-200" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Top Display -->
                    <rect x="8" y="2.5" width="12" height="4" rx="1.5"></rect>
                    <!-- Body -->
                    <path d="M5.5 8.5h17a2.5 2.5 0 0 1 2.45 3l-1.5 10a2.5 2.5 0 0 1-2.45 2H7a2.5 2.5 0 0 1-2.45-2l-1.5-10a2.5 2.5 0 0 1 2.45-3z"></path>
                    <!-- Key dots row 1 (4 dots) -->
                    <circle cx="8" cy="13" r="0.9" fill="currentColor"></circle>
                    <circle cx="12" cy="13" r="0.9" fill="currentColor"></circle>
                    <circle cx="16" cy="13" r="0.9" fill="currentColor"></circle>
                    <circle cx="20" cy="13" r="0.9" fill="currentColor"></circle>
                    <!-- Key dots row 2 (3 dots) -->
                    <circle cx="10" cy="16.5" r="0.9" fill="currentColor"></circle>
                    <circle cx="14" cy="16.5" r="0.9" fill="currentColor"></circle>
                    <circle cx="18" cy="16.5" r="0.9" fill="currentColor"></circle>
                    <!-- Drawer slot line -->
                    <line x1="7.5" y1="20" x2="20.5" y2="20"></line>
                </svg>

                <span class="absolute left-full ml-3 px-2.5 py-1 bg-neutral-900 text-white text-xs font-semibold rounded-md opacity-0 pointer-events-none group-hover:opacity-100 transition whitespace-nowrap z-50 shadow-xl border border-neutral-800">
                    Kasir & Transaksi
                </span>
            </a>
        </div>

        {{-- Menu khusus Admin (Kategori, Produk, Pengguna) --}}
        @if (Auth::check() && Auth::user()->role === 'admin')
            <!-- White dash separator -->
            <div class="w-6 h-[2.5px] bg-white rounded-full my-4"></div>

            <!-- Subtle divider line -->
            <div class="w-full h-[1px] bg-neutral-900/90 my-1"></div>

            <!-- 3. Kategori -->
            @php
                $isKategori = request()->routeIs('kategori*');
            @endphp
            <div class="w-full flex justify-center py-1">
                <a 
                    href="{{ route('kategori.index') }}" 
                    class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 relative group {{ $isKategori ? 'text-white' : 'text-neutral-500 hover:text-white' }}"
                    title="Kategori Produk"
                >
                    <!-- Geometric Shapes: Top Triangle + Bottom-Left Square + Bottom-Right Circle -->
                    <svg class="w-7 h-7 transition-colors duration-200" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Top Triangle -->
                        <polygon points="14,2.5 21.5,13.5 6.5,13.5"></polygon>
                        <!-- Bottom-Left Square -->
                        <rect x="3.5" y="16.5" width="8.5" height="8.5" rx="2"></rect>
                        <!-- Bottom-Right Circle -->
                        <circle cx="20.5" cy="20.75" r="4.75"></circle>
                    </svg>

                    <span class="absolute left-full ml-3 px-2.5 py-1 bg-neutral-900 text-white text-xs font-semibold rounded-md opacity-0 pointer-events-none group-hover:opacity-100 transition whitespace-nowrap z-50 shadow-xl border border-neutral-800">
                        Kategori
                    </span>
                </a>
            </div>

            <!-- Subtle divider line -->
            <div class="w-full h-[1px] bg-neutral-900/90 my-2"></div>

            <!-- 4. Produk / Inventaris -->
            @php
                $isProduk = request()->routeIs('produk*');
            @endphp
            <div class="w-full flex justify-center py-1">
                <a 
                    href="{{ route('produk.index') }}" 
                    class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 relative group {{ $isProduk ? 'text-white' : 'text-neutral-500 hover:text-white' }}"
                    title="Produk & Inventaris"
                >
                    <!-- 3D Box / Isometric Cube with Tape -->
                    <svg class="w-7 h-7 transition-colors duration-200" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Top diamond/rhombus -->
                        <polygon points="14,2.5 25,8.5 14,14.5 3,8.5"></polygon>
                        <!-- Center tape line across top face -->
                        <line x1="8.5" y1="5.5" x2="19.5" y2="11.5"></line>
                        <!-- Left face -->
                        <polyline points="3,8.5 3,20.5 14,26.5 14,14.5"></polyline>
                        <!-- Right face -->
                        <polyline points="25,8.5 25,20.5 14,26.5"></polyline>
                    </svg>

                    <span class="absolute left-full ml-3 px-2.5 py-1 bg-neutral-900 text-white text-xs font-semibold rounded-md opacity-0 pointer-events-none group-hover:opacity-100 transition whitespace-nowrap z-50 shadow-xl border border-neutral-800">
                        Produk & Inventaris
                    </span>
                </a>
            </div>

            <!-- Subtle divider line -->
            <div class="w-full h-[1px] bg-neutral-900/90 my-2"></div>

            <!-- 5. Varian Produk -->
            @php
                $isVarian = request()->routeIs('varian*');
            @endphp
            <div class="w-full flex justify-center py-1">
                <a 
                    href="{{ route('varian.index') }}" 
                    class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 relative group {{ $isVarian ? 'text-white' : 'text-neutral-500 hover:text-white' }}"
                    title="Varian Produk"
                >
                    <!-- 3 Stacked Isometric Cubes -->
                    <svg class="w-7 h-7 transition-colors duration-200" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Top Cube -->
                        <path d="M14 2.5L19.5 5.5L14 8.5L8.5 5.5Z"></path>
                        <path d="M8.5 5.5V11.5L14 14.5V8.5"></path>
                        <path d="M19.5 5.5V11.5L14 14.5"></path>
                        <!-- Bottom Left Cube -->
                        <path d="M8.5 13.5L14 16.5L8.5 19.5L3 16.5Z"></path>
                        <path d="M3 16.5V22.5L8.5 25.5V19.5"></path>
                        <path d="M14 16.5V22.5L8.5 25.5"></path>
                        <!-- Bottom Right Cube -->
                        <path d="M19.5 13.5L25 16.5L19.5 19.5L14 16.5Z"></path>
                        <path d="M14 16.5V22.5L19.5 25.5V19.5"></path>
                        <path d="M25 16.5V22.5L19.5 25.5"></path>
                    </svg>

                    <span class="absolute left-full ml-3 px-2.5 py-1 bg-neutral-900 text-white text-xs font-semibold rounded-md opacity-0 pointer-events-none group-hover:opacity-100 transition whitespace-nowrap z-50 shadow-xl border border-neutral-800">
                        Varian Produk
                    </span>
                </a>
            </div>

            <!-- Subtle divider line -->
            <div class="w-full h-[1px] bg-neutral-900/90 my-2"></div>

            <!-- 5. Pengguna / Pelanggan -->
            @php
                $isPengguna = request()->routeIs('pengguna*');
            @endphp
            <div class="w-full flex justify-center py-1">
                <a 
                    href="{{ route('pengguna.index') }}" 
                    class="w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-200 relative group {{ $isPengguna ? 'text-white' : 'text-neutral-500 hover:text-white' }}"
                    title="Pengguna & Pelanggan"
                >
                    <!-- Users Icon: Front silhouette white + back silhouette muted -->
                    <svg class="w-7 h-7 transition-colors duration-200" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Back user silhouette (offset to right) -->
                        <path d="M19 6a4.5 4.5 0 0 1 1.2 8.5" stroke="#737373"></path>
                        <path d="M19.5 21a7 7 0 0 1 3.2 4.5" stroke="#737373"></path>
                        <!-- Front user silhouette -->
                        <circle cx="11" cy="9.5" r="4.5"></circle>
                        <path d="M4 25.5a7 7 0 0 1 14 0"></path>
                    </svg>

                    <span class="absolute left-full ml-3 px-2.5 py-1 bg-neutral-900 text-white text-xs font-semibold rounded-md opacity-0 pointer-events-none group-hover:opacity-100 transition whitespace-nowrap z-50 shadow-xl border border-neutral-800">
                        Pengguna / Pelanggan
                    </span>
                </a>
            </div>
        @endif
    </nav>

    <!-- Spacer to push Logout to bottom -->
    <div class="flex-1"></div>

    <!-- Divider line above Logout -->
    <div class="w-full h-[1px] bg-neutral-900/90 my-2"></div>

    <!-- Bottom: Logout Button -->
    <div class="w-full flex justify-center py-1 mb-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button 
                type="submit" 
                class="w-11 h-11 rounded-xl flex items-center justify-center text-neutral-400 hover:text-red-400 transition-all duration-200 relative group cursor-pointer"
                title="Keluar / Logout"
            >
                <!-- Door Frame with Arrow Pointing Right -->
                <svg class="w-7 h-7" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Door Frame -->
                    <path d="M15 4H8a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h7"></path>
                    <!-- Arrow Right -->
                    <line x1="10" y1="14" x2="23" y2="14"></line>
                    <polyline points="18 9 23 14 18 19"></polyline>
                </svg>

                <span class="absolute left-full ml-3 px-2.5 py-1 bg-neutral-900 text-white text-xs font-semibold rounded-md opacity-0 pointer-events-none group-hover:opacity-100 transition whitespace-nowrap z-50 shadow-xl border border-neutral-800">
                    Keluar / Logout
                </span>
            </button>
        </form>
    </div>
</aside>
