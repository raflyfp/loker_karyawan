<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sistem Manajemen Loker — SNA Medika</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- SweetAlert2 Fallback CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body
    class="bg-[#f8fafc] text-[#0f172a] min-h-screen flex flex-col antialiased selection:bg-[#d92534] selection:text-white">

    <!-- HEADER & STATUS BAR -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 flex flex-col md:flex-row items-center justify-between gap-4">

            <!-- Logo & Title -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('icon/logo-sna-medika.jpg') }}" alt="Logo SNA Medika"
                    class="h-10 w-auto object-contain rounded-sm">
                <div>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight text-[#0f172a] leading-tight">
                        SISTEM MANAJEMEN LOKER
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">SNA Medika</p>
                </div>
            </div>

            <!-- Scanner Input & Controls -->
            <div class="flex items-center gap-2 w-full md:w-auto justify-end">
                <div class="relative flex-1 md:w-80">
                    <input type="text" id="barcodeInput" placeholder="Scan kartu barcode di sini..."
                        autocomplete="off" autofocus
                        class="w-full pl-3 pr-20 py-2 text-sm bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#d92534] focus:border-[#d92534] font-medium">
                    <button type="button" id="manualScanBtn"
                        class="absolute right-1 top-1 bottom-1 px-3 text-xs font-semibold bg-slate-800 text-white rounded hover:bg-slate-700 transition">
                        Scan
                    </button>
                </div>

                <button type="button" id="openCameraBtn"
                    class="px-3 py-2 text-sm font-semibold bg-[#0060b2] hover:bg-[#004d8f] text-white rounded transition flex items-center gap-1.5 shrink-0"
                    title="Gunakan Kamera Browser">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Kamera</span>
                </button>

                @auth
                    <a href="{{ route('admin.dashboard') }}"
                        class="px-3 py-2 text-sm font-bold bg-slate-900 hover:bg-slate-800 text-white rounded transition flex items-center gap-1.5 shrink-0 shadow-xs"
                        title="Buka Panel Admin Master Data">
                        <svg class="w-4 h-4 text-[#d92534]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Admin Panel</span>
                    </a>
                @else
                    <a href="{{ route('admin.login') }}"
                        class="px-3 py-2 text-sm font-semibold bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 rounded transition flex items-center gap-1.5 shrink-0 shadow-2xs"
                        title="Buka Halaman Login Administrator">
                        <svg class="w-4 h-4 text-[#d92534]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Login Admin</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Live Sub-status bar -->
        <div class="bg-slate-100 border-t border-slate-200 px-4 py-1.5 sm:px-6">
            <div class="max-w-7xl mx-auto flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    {{-- <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> --}}
                    {{-- <span id="statusIndicator" class="font-medium text-slate-600">Siap memindai kartu barcode...</span> --}}
                </div>
                <!-- Legend -->
                <div class="hidden sm:flex items-center gap-4 text-slate-500 text-[11px]">
                    <span class="flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded bg-emerald-100 border border-emerald-300"></span> Tersedia</span>
                    <span class="flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded bg-rose-100 border border-rose-300"></span> Terisi</span>
                    <span class="flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded bg-slate-200 border border-slate-300"></span> Terkunci</span>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT: DUA LAYER PETA LOKER -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 flex flex-col gap-6">

        <!-- LAYER ATAS: LOKER LAKI-LAKI (1 s/d 100) -->
        <section class="bg-white border border-slate-200 rounded-md p-4 sm:p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#0060b2]"></span>
                    <h2 class="text-sm sm:text-base font-bold uppercase tracking-wider text-slate-900">
                        LOKER LAKI-LAKI
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <span id="maleAvailableCount"
                        class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $maleAvailable }} / 100 Tersedia
                    </span>
                </div>
            </div>

            <!-- Grid 1..100 Laki-laki -->
            <div class="male-grid grid grid-cols-10 sm:grid-cols-12 md:grid-cols-20 gap-1.5 sm:gap-2">
                @foreach ($maleLockers as $loker)
                    @php
                        $isOccupied = $loker->alokasiAktif !== null;
                        $statusClass = !$loker->is_active
                            ? 'status-disabled'
                            : ($isOccupied
                                ? 'status-occupied'
                                : 'status-available');
                        $karyawanName = $isOccupied ? $loker->alokasiAktif->karyawan->nama : '';
                    @endphp
                    <div class="loker-box {{ $statusClass }}" data-id="{{ $loker->id }}"
                        data-kode="{{ $loker->kode_loker }}" data-nomor="{{ $loker->nomor_urut }}" data-gender="L"
                        data-status="{{ !$loker->is_active ? 'disabled' : ($isOccupied ? 'occupied' : 'available') }}"
                        data-karyawan="{{ $karyawanName }}"
                        title="Loker {{ $loker->kode_loker }} {{ $isOccupied ? '· ' . $karyawanName : (!$loker->is_active ? '· Dinonaktifkan' : '· Tersedia') }}">
                        {{ $loker->nomor_urut }}
                    </div>
                @endforeach
            </div>
        </section>

        <!-- LAYER BAWAH: LOKER PEREMPUAN (1 s/d 100) -->
        <section class="bg-white border border-slate-200 rounded-md p-4 sm:p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-[#d92534]"></span>
                    <h2 class="text-sm sm:text-base font-bold uppercase tracking-wider text-slate-900">
                        LOKER PEREMPUAN
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <span id="femaleAvailableCount"
                        class="text-xs font-semibold px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">
                        {{ $femaleAvailable }} / 100 Tersedia
                    </span>
                </div>
            </div>

            <!-- Grid 1..100 Perempuan -->
            <div class="female-grid grid grid-cols-10 sm:grid-cols-12 md:grid-cols-20 gap-1.5 sm:gap-2">
                @foreach ($femaleLockers as $loker)
                    @php
                        $isOccupied = $loker->alokasiAktif !== null;
                        $statusClass = !$loker->is_active
                            ? 'status-disabled'
                            : ($isOccupied
                                ? 'status-occupied'
                                : 'status-available');
                        $karyawanName = $isOccupied ? $loker->alokasiAktif->karyawan->nama : '';
                    @endphp
                    <div class="loker-box {{ $statusClass }}" data-id="{{ $loker->id }}"
                        data-kode="{{ $loker->kode_loker }}" data-nomor="{{ $loker->nomor_urut }}" data-gender="P"
                        data-status="{{ !$loker->is_active ? 'disabled' : ($isOccupied ? 'occupied' : 'available') }}"
                        data-karyawan="{{ $karyawanName }}"
                        title="Loker {{ $loker->kode_loker }} {{ $isOccupied ? '· ' . $karyawanName : (!$loker->is_active ? '· Dinonaktifkan' : '· Tersedia') }}">
                        {{ $loker->nomor_urut }}
                    </div>
                @endforeach
            </div>
        </section>

    </main>

    <!-- FOOTER INFO -->
    <footer class="bg-white border-t border-slate-200 py-3 text-center text-xs text-slate-500">
        <p>Sistem Pemilihan Loker Otomatis &copy; {{ date('Y') }} PT SNA Medika. Sentuh kotak loker untuk melihat
            rincian.</p>
    </footer>

    <!-- ======================================================= -->
    <!-- MODAL 1: HASIL TRANSAKSI SCAN (ALOKASI / PELEPASAN)       -->
    <!-- ======================================================= -->
    <div id="resultModal"
        class="hidden fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6">
        <div
            class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-8 sm:p-10 text-center flex flex-col items-center gap-5 shadow-2xl transition-all">

            <h3 id="resultTitle" class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                Terima Kasih
            </h3>

            <!-- NOMOR LOKER BESAR -->
            <div class="py-2 w-full">
                <span class="text-xs sm:text-sm uppercase font-bold text-slate-400 block tracking-wider mb-2">Nomor
                    Loker Anda</span>
                <span id="resultLockerNumber"
                    class="text-8xl sm:text-9xl font-black text-[#0060b2] tracking-tight leading-none block my-2">
                    4
                </span>
                <span id="resultLockerCode" class="block text-base sm:text-lg font-bold text-slate-600 mt-2">
                    Loker L-4 (Laki-laki)
                </span>
                <div id="resultStatusSubtitle"
                    class="mt-3 text-base sm:text-lg font-extrabold text-emerald-600 px-4 py-1.5 bg-emerald-50 border border-emerald-200 rounded-full inline-block">
                    Kunci Berhasil Dikembalikan
                </div>
            </div>

            <!-- Nama Karyawan -->
            <div class="border-t border-slate-100 pt-4 w-full">
                <span
                    class="text-xs sm:text-sm text-slate-400 uppercase font-semibold tracking-wider block">Karyawan</span>
                <span id="resultKaryawanName" class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5 block">
                    Nama Karyawan
                </span>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 2: PILIH GENDER (JIKA MASTER GENDER NULL)          -->
    <!-- ======================================================= -->
    <div id="genderModal"
        class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div
            class="bg-white rounded-lg border border-slate-200 max-w-md w-full p-6 text-center flex flex-col items-center gap-4">
            <span class="px-3 py-1 text-xs font-bold uppercase rounded-sm bg-amber-100 text-amber-800">
                KONFIRMASI KELOMPOK
            </span>

            <div>
                <h3 id="genderKaryawanName" class="text-lg font-bold text-slate-900">
                    Nama Karyawan
                </h3>
                <p id="genderKaryawanDesc" class="text-xs text-slate-600 mt-1">
                    Data kelompok loker belum ditentukan. Silakan pilih kelompok loker Anda:
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 w-full mt-2">
                <button type="button" id="chooseMaleBtn"
                    class="py-4 px-4 bg-[#0060b2] hover:bg-[#004d8f] text-white rounded font-bold text-base flex flex-col items-center justify-center gap-1 transition shadow-sm">
                    <span class="text-2xl">♂️</span>
                    <span>LAKI-LAKI</span>
                    <span class="text-[10px] font-normal text-blue-100">Loker Atas (1—100)</span>
                </button>

                <button type="button" id="chooseFemaleBtn"
                    class="py-4 px-4 bg-[#d92534] hover:bg-[#b91c2b] text-white rounded font-bold text-base flex flex-col items-center justify-center gap-1 transition shadow-sm">
                    <span class="text-2xl">♀️</span>
                    <span>PEREMPUAN</span>
                    <span class="text-[10px] font-normal text-rose-100">Loker Bawah (1—100)</span>
                </button>
            </div>

            <button type="button" id="cancelGenderBtn"
                class="text-xs text-slate-500 hover:text-slate-800 transition py-1">
                Batal
            </button>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 3: DETAIL LOKER (INFORMASI UNTUK KARYAWAN)        -->
    <!-- ======================================================= -->
    <div id="detailModal"
        class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg border border-slate-200 max-w-sm w-full p-5 flex flex-col gap-4 shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#0060b2]"></div>
                    <h3 class="text-sm font-bold text-slate-900">Informasi Loker</h3>
                </div>
                <span id="detailLockerCode"
                    class="text-xs font-bold px-2 py-0.5 bg-slate-100 rounded text-slate-700 font-mono">L-01</span>
            </div>

            <div class="text-xs space-y-2.5">
                <div class="flex justify-between items-center py-1 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Nomor Loker:</span>
                    <span id="detailLockerNumber" class="font-extrabold text-slate-900 text-base">1</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Kelompok:</span>
                    <span id="detailLockerGender" class="font-semibold text-slate-900">Laki-laki</span>
                </div>
                <div class="flex justify-between items-center py-1 border-b border-slate-100">
                    <span class="text-slate-500 font-medium">Status Saat Ini:</span>
                    <span id="detailLockerStatus" class="font-bold text-slate-900">Tersedia</span>
                </div>
            </div>

            <div
                class="p-2.5 rounded bg-slate-50 border border-slate-200 text-[11px] text-slate-500 flex items-start gap-2">
                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Penguncian atau perubahan status loker hanya dapat dilakukan oleh <strong>Administrator</strong>
                    melalui Panel Admin.</span>
            </div>

            <div class="pt-1">
                <button type="button" id="closeDetailBtn"
                    class="w-full py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded transition shadow-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- MODAL 4: KAMERA BARCODE SCANNER                         -->
    <!-- ======================================================= -->
    <div id="cameraModal"
        class="hidden fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-lg border border-slate-200 max-w-md w-full p-5 flex flex-col items-center gap-3">
            <div class="flex items-center justify-between w-full border-b border-slate-100 pb-2">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                    <span>Arahkan Barcode ke Kamera</span>
                </h3>
                <button type="button" id="closeCameraBtn"
                    class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
            </div>

            <!-- Kamera Box Reader -->
            <div id="cameraReader" class="w-full overflow-hidden rounded bg-black aspect-square"></div>

            <p class="text-xs text-slate-500 text-center">
                Posisikan barcode kartu ID karyawan di dalam kotak pemindai.
            </p>
        </div>
    </div>

</body>

</html>
