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
    <!-- SweetAlert2 Fallback CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body
    class="bg-slate-100 text-slate-800 min-h-screen font-sans antialiased selection:bg-[#d92534] selection:text-white">

    <!-- MOBILE BACKDROP -->
    <div id="sidebarBackdrop"
        class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs hidden md:hidden transition-opacity"></div>

    <!-- SIDEBAR -->
    <aside id="adminSidebar"
        class="fixed top-0 bottom-0 left-0 z-50 w-64 bg-slate-900 text-slate-200 flex flex-col border-r border-slate-800 transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0">

        <!-- Sidebar Brand Header -->
        <div class="h-16 px-5 flex items-center justify-between border-b border-slate-800 bg-slate-950/60 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('icon/logo-sna-medika.jpg') }}" alt="Logo SNA Medika"
                    class="h-9 w-auto rounded object-contain bg-white p-0.5 shadow-xs">
                <div>
                    <div class="flex items-center gap-1.5">
                        <span
                            class="font-extrabold text-sm tracking-tight text-white group-hover:text-red-400 transition">ADMIN</span>
                    </div>
                    <p class="text-[10px] text-slate-400 font-medium">PT SNA Medika</p>
                </div>
            </a>
            <!-- Close button for mobile -->
            <button type="button" id="closeSidebarBtn"
                class="md:hidden text-slate-400 hover:text-white p-1 rounded-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation Menu -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">

            <!-- Section: Menu Utama -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Menu Utama
                </div>
                <nav class="space-y-1 text-xs font-medium">
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-[#d92534] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard & Loker</span>
                    </a>

                    <!-- Master Karyawan -->
                    <a href="{{ route('admin.karyawan.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.karyawan.*') ? 'bg-[#d92534] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.karyawan.*') ? 'text-white' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Master Karyawan</span>
                    </a>

                    <!-- Master Loker -->
                    <a href="{{ route('admin.loker.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.loker.*') ? 'bg-[#d92534] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.loker.*') ? 'text-white' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>Master Data Loker</span>
                    </a>

                </nav>
            </div>

            <!-- Section: Laporan & Analisis -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Laporan & Rekap
                </div>
                <nav class="space-y-1 text-xs font-medium">
                    <!-- Report Pemakaian -->
                    <a href="{{ route('admin.report.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.report.*') ? 'bg-[#d92534] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('admin.report.*') ? 'text-white' : 'text-slate-400' }}"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Laporan Pemakaian</span>
                    </a>
                </nav>
            </div>

            <!-- Section: Pintasan Cepat -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                    Pintasan Layar
                </div>
                <div class="space-y-1">
                    <a href="{{ route('loker.index') }}" target="_blank"
                        class="flex items-center justify-between px-3 py-2.5 text-xs font-semibold rounded-lg text-blue-300 bg-blue-950/40 border border-blue-900/50 hover:bg-blue-900/60 hover:text-white transition">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Layar Loker Karyawan</span>
                        </div>
                        <span class="text-xs">↗</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Sidebar User Profile & Logout -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/50 shrink-0">
            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-900/80 border border-slate-800/80">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div
                        class="w-8 h-8 rounded-full bg-[#0060b2] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono truncate">
                            {{ Auth::user()->username ?? 'admin' }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.logout') }}" class="m-0">
                    @csrf
                    <button type="submit"
                        data-confirm="Apakah Anda yakin ingin keluar dari Admin Panel?"
                        data-confirm-title="Konfirmasi Logout"
                        data-confirm-text="Ya, Keluar"
                        data-cancel-text="Batal"
                        data-confirm-danger="true"
                        class="p-1.5 text-rose-400 hover:text-white hover:bg-rose-600/30 rounded-md transition"
                        title="Keluar / Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <!-- MAIN CONTENT WRAPPER -->
    <div class="md:pl-64 flex flex-col min-h-screen">

        <!-- TOPBAR -->
        <header
            class="sticky top-0 z-30 bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-2xs">

            <!-- Left: Mobile Menu Trigger & Breadcrumb Title -->
            <div class="flex items-center gap-3">
                <button type="button" id="openSidebarBtn"
                    class="md:hidden p-2 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400 hidden sm:inline">Admin Panel</span>
                    <span class="text-xs text-slate-300 hidden sm:inline">/</span>
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
                        @yield('page_title', 'Master Data Loker')
                    </h1>
                </div>
            </div>



        </header>



        <!-- MAIN PAGE CONTENT -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-5">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
            <div class="max-w-7xl mx-auto px-4">
                Sistem Manajemen Loker Otomatis &copy; {{ date('Y') }} PT SNA Medika. Administrator Control Panel.
            </div>
        </footer>

    </div>

    <!-- SIDEBAR MOBILE TOGGLE & SWEETALERT2 GLOBAL SCRIPTS -->
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const openBtn = document.getElementById('openSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function openSidebar() {
            sidebar?.classList.remove('-translate-x-full');
            backdrop?.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar?.classList.add('-translate-x-full');
            backdrop?.classList.add('hidden');
        }

        openBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        backdrop?.addEventListener('click', closeSidebar);

        // Global SweetAlert2 Confirm Handler for any element with data-confirm
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('[data-confirm]');
            if (!trigger) return;

            e.preventDefault();
            e.stopPropagation();

            const message = trigger.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            const title = trigger.getAttribute('data-confirm-title') || 'Konfirmasi';
            const confirmText = trigger.getAttribute('data-confirm-text') || 'Ya, Lanjutkan';
            const cancelText = trigger.getAttribute('data-cancel-text') || 'Batal';
            const isDanger = trigger.getAttribute('data-confirm-danger') !== 'false';

            if (window.Swal) {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: isDanger ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: isDanger ? '#d92534' : '#0060b2',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: confirmText,
                    cancelButtonText: cancelText,
                    reverseButtons: true,
                    focusCancel: isDanger,
                    borderRadius: '8px',
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = trigger.form || trigger.closest('form');
                        if (trigger.tagName === 'A') {
                            window.location.href = trigger.href;
                        } else if (form) {
                            form.submit();
                        }
                    }
                });
            } else {
                if (confirm(message)) {
                    const form = trigger.form || trigger.closest('form');
                    if (trigger.tagName === 'A') {
                        window.location.href = trigger.href;
                    } else if (form) {
                        form.submit();
                    }
                }
            }
        });
    </script>

    <!-- SweetAlert2 Flash Notifications -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: @json(session('success')),
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });
                }
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian',
                        text: @json(session('error')),
                        confirmButtonColor: '#d92534'
                    });
                }
            });
        </script>
    @endif

    @if (isset($errors) && $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Periksa Data Formulir',
                        html: `{!! implode('<br>', array_map('e', $errors->all())) !!}`,
                        confirmButtonColor: '#d92534'
                    });
                }
            });
        </script>
    @endif

    @stack('scripts')
</body>

</html>
