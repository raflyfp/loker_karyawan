<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') — Sistem Manajemen Loker SNA Medika</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col font-sans antialiased selection:bg-[#d92534] selection:text-white">

    <!-- TOP NAVIGATION -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-4">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('icon/logo-sna-medika.jpg') }}" alt="Logo SNA Medika" class="h-9 w-auto rounded object-contain">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-extrabold text-base tracking-tight text-slate-900 group-hover:text-[#d92534] transition">ADMIN LOKER</span>
                                <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-red-100 text-[#d92534]">Panel</span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium">PT SNA Medika</p>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-1 font-medium text-sm">
                    <a href="{{ route('admin.dashboard') }}"
                        class="px-3 py-2 rounded-md transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.karyawan.index') }}"
                        class="px-3 py-2 rounded-md transition {{ request()->routeIs('admin.karyawan.*') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Master Karyawan
                    </a>
                    <a href="{{ route('admin.loker.index') }}"
                        class="px-3 py-2 rounded-md transition {{ request()->routeIs('admin.loker.*') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Master Loker
                    </a>
                    <a href="{{ route('admin.alokasi.index') }}"
                        class="px-3 py-2 rounded-md transition {{ request()->routeIs('admin.alokasi.*') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Data Alokasi
                    </a>
                </nav>

                <!-- Right Quick Actions: Kiosk Link & User Info -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('loker.index') }}" target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[#0060b2] bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition"
                        title="Buka Layar Kiosk Scanner Loker">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Buka Kiosk</span>
                    </a>

                    <!-- User / Logout Dropdown -->
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
                        <div class="hidden sm:block text-right">
                            <div class="text-xs font-bold text-slate-900 leading-tight">{{ Auth::user()->name ?? 'Administrator' }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ Auth::user()->username ?? 'admin' }}</div>
                        </div>

                        <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                onclick="return confirm('Apakah Anda yakin ingin keluar dari Admin Panel?');"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 rounded-md hover:bg-rose-100 transition"
                                title="Keluar / Logout">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button type="button" id="mobileMenuBtn" class="md:hidden p-2 rounded text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-2 pb-3 space-y-1 text-sm font-medium">
            <a href="{{ route('admin.dashboard') }}"
                class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                Dashboard
            </a>
            <a href="{{ route('admin.karyawan.index') }}"
                class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.karyawan.*') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                Master Karyawan
            </a>
            <a href="{{ route('admin.loker.index') }}"
                class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.loker.*') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                Master Loker
            </a>
            <a href="{{ route('admin.alokasi.index') }}"
                class="block px-3 py-2 rounded-md {{ request()->routeIs('admin.alokasi.*') ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                Data Alokasi
            </a>
            <a href="{{ route('loker.index') }}" target="_blank"
                class="block px-3 py-2 rounded-md text-[#0060b2] hover:bg-blue-50 font-semibold">
                ↗ Buka Layar Kiosk Scanner
            </a>
        </div>
    </header>

    <!-- FLASH MESSAGES -->
    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div class="flex items-center justify-between p-3.5 mb-3 text-sm font-medium text-emerald-800 bg-emerald-50 border border-emerald-300 rounded-md">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold ml-2">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center justify-between p-3.5 mb-3 text-sm font-medium text-rose-800 bg-rose-50 border border-rose-300 rounded-md">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-950 font-bold ml-2">&times;</button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="p-3.5 mb-3 text-sm text-rose-800 bg-rose-50 border border-rose-300 rounded-md">
                <div class="font-semibold mb-1">Terdapat kesalahan validasi:</div>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- MAIN PAGE CONTENT -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            Sistem Manajemen Loker Otomatis &copy; {{ date('Y') }} PT SNA Medika. Administrator Control Panel.
        </div>
    </footer>

    <script>
        // Toggle mobile menu
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>

    @stack('scripts')
</body>

</html>
