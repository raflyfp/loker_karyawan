@extends('layouts.admin')

@section('title', 'Dashboard & Visualisasi Loker')
@section('page_title', 'Dashboard & Monitoring Loker')

@section('content')
    <div class="space-y-6">

        <!-- Header -->
        {{-- <div class="pb-2 border-b border-slate-200">
        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard & Visualisasi Loker</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pemantauan real-time kapasitas loker SNA Medika dan kontrol kunci/buka loker.</p>
    </div> --}}

        <!-- METRIC CARDS: KAPASITAS LOKER LAKI-LAKI & PEREMPUAN -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Card 1: Loker Laki-laki -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0060b2]"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#0060b2]">Kapasitas Loker
                            Laki-laki</span>
                    </div>
                    {{-- <span
                        class="text-[11px] font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                        Total {{ $maleTotal }} Loker
                    </span> --}}
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div>
                        <span id="statMaleAvailable"
                            class="text-3xl sm:text-4xl font-extrabold text-slate-900">{{ $maleAvailable }}</span>
                        <span class="text-xs text-slate-500 font-medium ml-1">Tersedia</span>
                    </div>
                    <div class="text-right text-xs space-y-0.5">
                        <div id="statMaleOccupied" class="text-rose-600 font-semibold">{{ $maleOccupied }} Terisi</div>
                        <div id="statMaleDisabled" class="text-slate-400">{{ $maleDisabled }} Dikunci (Maintenance)</div>
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-3.5 overflow-hidden flex">
                    @php
                        $maleOccPct = $maleTotal > 0 ? ($maleOccupied / $maleTotal) * 100 : 0;
                        $maleDisPct = $maleTotal > 0 ? ($maleDisabled / $maleTotal) * 100 : 0;
                    @endphp
                    <div id="barMaleOccupied" class="bg-rose-500 h-2 transition-all duration-300" style="width: {{ $maleOccPct }}%" title="Terisi: {{ $maleOccupied }}">
                    </div>
                    <div id="barMaleDisabled" class="bg-slate-400 h-2 transition-all duration-300" style="width: {{ $maleDisPct }}%" title="Dikunci: {{ $maleDisabled }}">
                    </div>
                </div>
            </div>

            <!-- Card 2: Loker Perempuan -->
            <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-2xs">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#d92534]"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#d92534]">Kapasitas Loker
                            Perempuan</span>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <div>
                        <span id="statFemaleAvailable"
                            class="text-3xl sm:text-4xl font-extrabold text-slate-900">{{ $femaleAvailable }}</span>
                        <span class="text-xs text-slate-500 font-medium ml-1">Tersedia</span>
                    </div>
                    <div class="text-right text-xs space-y-0.5">
                        <div id="statFemaleOccupied" class="text-rose-600 font-semibold">{{ $femaleOccupied }} Terisi</div>
                        <div id="statFemaleDisabled" class="text-slate-400">{{ $femaleDisabled }} Dikunci (Maintenance)
                        </div>
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-3.5 overflow-hidden flex">
                    @php
                        $femOccPct = $femaleTotal > 0 ? ($femaleOccupied / $femaleTotal) * 100 : 0;
                        $femDisPct = $femaleTotal > 0 ? ($femaleDisabled / $femaleTotal) * 100 : 0;
                    @endphp
                    <div id="barFemaleOccupied" class="bg-rose-500 h-2 transition-all duration-300" style="width: {{ $femOccPct }}%"
                        title="Terisi: {{ $femaleOccupied }}"></div>
                    <div id="barFemaleDisabled" class="bg-slate-400 h-2 transition-all duration-300" style="width: {{ $femDisPct }}%"
                        title="Dikunci: {{ $femaleDisabled }}"></div>
                </div>
            </div>

        </div>

        <!-- ======================================================= -->
        <!-- VISUALISASI GRID LOKER (INTERACTIVE LOCK/UNLOCK)        -->
        <!-- ======================================================= -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs p-5 space-y-6" id="visualisasi-loker">

            <!-- Header & Legend Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-3 border-b border-slate-100">
                <div>
                    {{-- <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0060b2]"></span>
                        <h2 class="text-base font-bold text-slate-900">Visualisasi Status Loker</h2>
                    </div> --}}
                    <p class="text-xs text-slate-500 mt-0.5">
                        Klik pada kotak loker mana pun untuk melihat rincian pemakai atau <strong>mengubah status Kunci
                            (Lock / Unlock)</strong>.
                    </p>
                </div>

                <!-- Legend Status -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 rounded-sm bg-[#f0fdf4] border border-[#bbf7d0]"></span>
                        <span class="text-slate-600">Tersedia</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 rounded-sm bg-[#fef2f2] border border-[#fecaca]"></span>
                        <span class="text-slate-600">Terisi Karyawan</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span
                            class="w-3.5 h-3.5 rounded-sm bg-[#f1f5f9] border border-[#cbd5e1] text-slate-400 flex items-center justify-center text-[9px]">🔒</span>
                        <span class="text-slate-600">Dikunci (Maintenance)</span>
                    </div>
                </div>
            </div>

            <!-- LAYER ATAS: LOKER LAKI-LAKI (1 s/d 100) -->
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#0060b2]"></span>
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800">
                            Loker Laki-laki
                        </h3>
                    </div>
                    <span id="badgeMaleTersedia"
                        class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $maleAvailable }} / 100 Tersedia
                    </span>
                </div>

                <!-- Grid 1..100 Laki-laki -->
                <div class="grid grid-cols-10 sm:grid-cols-12 md:grid-cols-20 gap-1.5 sm:gap-2">
                    @foreach ($maleLockers as $loker)
                        @php
                            $isOccupied = $loker->alokasiAktif !== null;
                            $statusClass = !$loker->is_active
                                ? 'status-disabled'
                                : ($isOccupied
                                    ? 'status-occupied'
                                    : 'status-available');
                            $karyawanName = $isOccupied ? $loker->alokasiAktif->karyawan->nama ?? '' : '';
                            $karyawanNik = $isOccupied ? $loker->alokasiAktif->karyawan->nik ?? '' : '';
                            $alokasiId = $isOccupied ? $loker->alokasiAktif->id : '';
                        @endphp
                        <div class="loker-box {{ $statusClass }} locker-clickable text-xs"
                            id="locker-cell-{{ $loker->id }}" data-id="{{ $loker->id }}"
                            data-kode="{{ $loker->kode_loker }}" data-nomor="{{ $loker->nomor_urut }}" data-gender="L"
                            data-is-active="{{ $loker->is_active ? '1' : '0' }}"
                            data-status="{{ !$loker->is_active ? 'disabled' : ($isOccupied ? 'occupied' : 'available') }}"
                            data-karyawan="{{ $karyawanName }}" data-nik="{{ $karyawanNik }}"
                            data-alokasi-id="{{ $alokasiId }}" title="Klik untuk atur Loker {{ $loker->kode_loker }}">
                            <span>{{ $loker->nomor_urut }}</span>
                            @if (!$loker->is_active)
                                <span class="text-[9px] leading-none">🔒</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- LAYER BAWAH: LOKER PEREMPUAN (1 s/d 100) -->
            <section class="space-y-3 pt-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#d92534]"></span>
                        <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800">
                            Loker Perempuan
                        </h3>
                    </div>
                    <span id="badgeFemaleTersedia"
                        class="text-xs font-semibold px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">
                        {{ $femaleAvailable }} / 100 Tersedia
                    </span>
                </div>

                <!-- Grid 1..100 Perempuan -->
                <div class="grid grid-cols-10 sm:grid-cols-12 md:grid-cols-20 gap-1.5 sm:gap-2">
                    @foreach ($femaleLockers as $loker)
                        @php
                            $isOccupied = $loker->alokasiAktif !== null;
                            $statusClass = !$loker->is_active
                                ? 'status-disabled'
                                : ($isOccupied
                                    ? 'status-occupied'
                                    : 'status-available');
                            $karyawanName = $isOccupied ? $loker->alokasiAktif->karyawan->nama ?? '' : '';
                            $karyawanNik = $isOccupied ? $loker->alokasiAktif->karyawan->nik ?? '' : '';
                            $alokasiId = $isOccupied ? $loker->alokasiAktif->id : '';
                        @endphp
                        <div class="loker-box {{ $statusClass }} locker-clickable text-xs"
                            id="locker-cell-{{ $loker->id }}" data-id="{{ $loker->id }}"
                            data-kode="{{ $loker->kode_loker }}" data-nomor="{{ $loker->nomor_urut }}" data-gender="P"
                            data-is-active="{{ $loker->is_active ? '1' : '0' }}"
                            data-status="{{ !$loker->is_active ? 'disabled' : ($isOccupied ? 'occupied' : 'available') }}"
                            data-karyawan="{{ $karyawanName }}" data-nik="{{ $karyawanNik }}"
                            data-alokasi-id="{{ $alokasiId }}" title="Klik untuk atur Loker {{ $loker->kode_loker }}">
                            <span>{{ $loker->nomor_urut }}</span>
                            @if (!$loker->is_active)
                                <span class="text-[9px] leading-none">🔒</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

        </div>



    </div>

    <!-- ======================================================= -->
    <!-- MODAL KONTROL / ATUR STATUS LOKER (LOCK / UNLOCK)        -->
    <!-- ======================================================= -->
    <div id="lockerManageModal"
        class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div
            class="bg-white rounded-xl border border-slate-200 max-w-sm w-full p-5 shadow-xl text-center space-y-4 animate-in fade-in zoom-in-95 duration-150">

            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pengaturan Loker</span>
                <button type="button" id="closeManageModalBtn"
                    class="text-slate-400 hover:text-slate-700 text-lg leading-none p-1 font-bold">&times;</button>
            </div>

            <!-- Locker Big Badge -->
            <div class="py-1">
                <span id="modalLockerCategory"
                    class="text-[11px] font-bold uppercase tracking-wider text-[#0060b2] block mb-1">
                    Loker Laki-laki
                </span>
                <h3 id="modalLockerNumber" class="text-5xl font-extrabold text-slate-900 tracking-tight">
                    L-12
                </h3>
                <div id="modalStatusBadge"
                    class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold">
                    Tersedia
                </div>
            </div>

            <!-- Occupied Info Card (Shown only if occupied) -->
            <div id="modalOccupiedCard"
                class="hidden text-left p-3 rounded-lg bg-rose-50/80 border border-rose-200 text-xs space-y-1">
                <div class="text-[10px] uppercase font-bold text-rose-600">Sedang Digunakan Oleh:</div>
                <div id="modalKaryawanName" class="font-bold text-slate-900 text-sm">-</div>
                <div id="modalKaryawanNik" class="text-slate-500 font-mono text-[11px]">-</div>

                <form id="modalForceReleaseForm" method="POST" action="" class="pt-2">
                    @csrf
                    <button type="submit"
                        data-confirm="Lepaskan / checkout paksa loker ini sekarang? Penggunaan oleh karyawan akan diselesaikan dan loker kembali berstatus tersedia."
                        data-confirm-title="Checkout Paksa Loker" data-confirm-text="Ya, Checkout Paksa"
                        class="w-full py-1.5 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-semibold transition shadow-2xs">
                        Checkout Paksa Loker
                    </button>
                </form>
            </div>

            <!-- Status Explanation Note -->
            <p id="modalStatusNote" class="text-xs text-slate-500 px-2">
                Loker ini sedang aktif dan siap digunakan secara otomatis oleh karyawan.
            </p>

            <!-- Lock / Unlock Button Action -->
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                <button type="button" id="modalToggleLockBtn"
                    class="w-full py-2 px-4 rounded-lg font-bold text-xs transition flex items-center justify-center gap-2 shadow-2xs">
                    <span id="modalToggleIcon">🔒</span>
                    <span id="modalToggleText">Kunci Loker (Nonaktifkan)</span>
                </button>
                <button type="button" id="modalCancelBtn"
                    class="w-full py-2 px-3 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">
                    Tutup
                </button>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('lockerManageModal');
            const closeBtn = document.getElementById('closeManageModalBtn');
            const cancelBtn = document.getElementById('modalCancelBtn');
            const toggleLockBtn = document.getElementById('modalToggleLockBtn');
            const forceReleaseCard = document.getElementById('modalOccupiedCard');
            const forceReleaseForm = document.getElementById('modalForceReleaseForm');

            const modalCategory = document.getElementById('modalLockerCategory');
            const modalNumber = document.getElementById('modalLockerNumber');
            const modalStatusBadge = document.getElementById('modalStatusBadge');
            const modalStatusNote = document.getElementById('modalStatusNote');
            const modalToggleIcon = document.getElementById('modalToggleIcon');
            const modalToggleText = document.getElementById('modalToggleText');
            const modalKaryawanName = document.getElementById('modalKaryawanName');
            const modalKaryawanNik = document.getElementById('modalKaryawanNik');

            let currentLockerId = null;
            let currentLockerElement = null;

            function openModalForLocker(el) {
                currentLockerElement = el;
                currentLockerId = el.getAttribute('data-id');
                const kode = el.getAttribute('data-kode');
                const nomor = el.getAttribute('data-nomor');
                const gender = el.getAttribute('data-gender');
                const status = el.getAttribute('data-status');
                const isActive = el.getAttribute('data-is-active') === '1';
                const karyawan = el.getAttribute('data-karyawan') || '';
                const nik = el.getAttribute('data-nik') || '';
                const alokasiId = el.getAttribute('data-alokasi-id') || '';

                modalNumber.textContent = kode;
                modalCategory.textContent = gender === 'L' ? 'Loker Laki-laki' : 'Loker Perempuan';
                modalCategory.className = gender === 'L' ?
                    'text-[11px] font-bold uppercase tracking-wider text-[#0060b2] block mb-1' :
                    'text-[11px] font-bold uppercase tracking-wider text-[#d92534] block mb-1';

                if (status === 'occupied') {
                    // Loker Sedang Terisi
                    modalStatusBadge.textContent = 'Sedang Terisi';
                    modalStatusBadge.className =
                        'mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800';
                    modalStatusNote.textContent =
                        'Loker ini sedang aktif digunakan oleh karyawan. Untuk mengunci loker ini, lakukan checkout terlebih dahulu.';

                    modalKaryawanName.textContent = karyawan;
                    modalKaryawanNik.textContent = nik ? 'NIK: ' + nik : '-';
                    forceReleaseForm.action = `/admin/alokasi/${alokasiId}/force-release`;
                    forceReleaseCard.classList.remove('hidden');

                    toggleLockBtn.classList.add('hidden');
                } else if (!isActive || status === 'disabled') {
                    // Loker Terkunci / Maintenance
                    modalStatusBadge.textContent = '🔒 Dikunci (Maintenance)';
                    modalStatusBadge.className =
                        'mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700';
                    modalStatusNote.textContent =
                        'Loker ini sedang dikunci/maintenance dan tidak akan dipilihkan oleh sistem untuk karyawan.';

                    forceReleaseCard.classList.add('hidden');
                    toggleLockBtn.classList.remove('hidden');
                    toggleLockBtn.className =
                        'w-full py-2 px-4 rounded-lg font-bold text-xs transition flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white shadow-2xs cursor-pointer';
                    modalToggleIcon.textContent = '🔓';
                    modalToggleText.textContent = 'Buka Kunci Loker (Aktifkan)';
                } else {
                    // Loker Tersedia
                    modalStatusBadge.textContent = '✓ Tersedia';
                    modalStatusBadge.className =
                        'mt-2 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800';
                    modalStatusNote.textContent =
                        'Loker ini siap digunakan otomatis saat karyawan melakukan scan barcode.';

                    forceReleaseCard.classList.add('hidden');
                    toggleLockBtn.classList.remove('hidden');
                    toggleLockBtn.className =
                        'w-full py-2 px-4 rounded-lg font-bold text-xs transition flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white shadow-2xs cursor-pointer';
                    modalToggleIcon.textContent = '🔒';
                    modalToggleText.textContent = 'Kunci Loker (Nonaktifkan)';
                }

                modal.classList.remove('hidden');
            }

            function closeModal() {
                modal.classList.add('hidden');
            }

            // Event listener clicking on lockers
            document.querySelectorAll('.locker-clickable').forEach(box => {
                box.addEventListener('click', function() {
                    openModalForLocker(this);
                });
            });

            closeBtn?.addEventListener('click', closeModal);
            cancelBtn?.addEventListener('click', closeModal);
            modal?.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });

            // Toggle Lock AJAX Action
            toggleLockBtn?.addEventListener('click', async function() {
                if (!currentLockerId) return;

                toggleLockBtn.disabled = true;
                toggleLockBtn.classList.add('opacity-70');
                const originalText = modalToggleText.textContent;
                modalToggleText.textContent = 'Menyimpan...';

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content');
                    const response = await fetch(`/admin/loker/${currentLockerId}/toggle-active`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Update element UI
                        const isNowActive = data.is_active;
                        const nomor = currentLockerElement.getAttribute('data-nomor');
                        currentLockerElement.setAttribute('data-is-active', isNowActive ? '1' : '0');
                        currentLockerElement.setAttribute('data-status', isNowActive ? 'available' :
                            'disabled');

                        if (isNowActive) {
                            currentLockerElement.className =
                                'loker-box status-available locker-clickable text-xs';
                            currentLockerElement.innerHTML = `<span>${nomor}</span>`;
                        } else {
                            currentLockerElement.className =
                                'loker-box status-disabled locker-clickable text-xs';
                            currentLockerElement.innerHTML =
                                `<span>${nomor}</span><span class="text-[9px] leading-none">🔒</span>`;
                        }

                        // Update modal state in place
                        openModalForLocker(currentLockerElement);

                        if (window.Swal) {
                            window.Swal.fire({
                                icon: 'success',
                                title: 'Status Loker Diperbarui',
                                text: data.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }
                    } else {
                        if (window.Swal) {
                            window.Swal.fire({
                                icon: 'error',
                                title: 'Perhatian',
                                text: data.message || 'Gagal mengubah status kunci loker.',
                                confirmButtonColor: '#d92534'
                            });
                        } else {
                            alert(data.message || 'Gagal mengubah status kunci loker.');
                        }
                    }
                } catch (err) {
                    console.error(err);
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'error',
                            title: 'Kesalahan Jaringan',
                            text: 'Terjadi kesalahan koneksi saat mengunci/membuka loker.',
                            confirmButtonColor: '#d92534'
                        });
                    } else {
                        alert('Terjadi kesalahan koneksi saat mengunci/membuka loker.');
                    }
                } finally {
                    toggleLockBtn.disabled = false;
                    toggleLockBtn.classList.remove('opacity-70');
                }
            });

            // =======================================================
            // REAL-TIME AUTO-SYNC BACKGROUND POLLING (SETIAP 5 DETIK)
            // =======================================================
            async function syncLockerStatus() {
                try {
                    const res = await fetch('/api/loker/status');
                    const data = await res.json();
                    if (!data.success) return;

                    // Update summary stats & progress bars
                    const sum = data.summary;
                    const maleAvailEl = document.getElementById('statMaleAvailable');
                    const maleOccEl = document.getElementById('statMaleOccupied');
                    const maleDisEl = document.getElementById('statMaleDisabled');
                    const badgeMale = document.getElementById('badgeMaleTersedia');
                    const barMaleOcc = document.getElementById('barMaleOccupied');
                    const barMaleDis = document.getElementById('barMaleDisabled');

                    if (maleAvailEl) maleAvailEl.textContent = sum.available_male;
                    if (maleOccEl) maleOccEl.textContent = `${sum.occupied_male} Terisi`;
                    if (maleDisEl) maleDisEl.textContent = `${sum.disabled_male} Dikunci (Maintenance)`;
                    if (badgeMale) badgeMale.textContent = `${sum.available_male} / 100 Tersedia`;
                    if (barMaleOcc) {
                        const pct = sum.total_male > 0 ? (sum.occupied_male / sum.total_male) * 100 : 0;
                        barMaleOcc.style.width = pct + '%';
                        barMaleOcc.setAttribute('title', `Terisi: ${sum.occupied_male}`);
                    }
                    if (barMaleDis) {
                        const pct = sum.total_male > 0 ? (sum.disabled_male / sum.total_male) * 100 : 0;
                        barMaleDis.style.width = pct + '%';
                        barMaleDis.setAttribute('title', `Dikunci: ${sum.disabled_male}`);
                    }

                    const femaleAvailEl = document.getElementById('statFemaleAvailable');
                    const femaleOccEl = document.getElementById('statFemaleOccupied');
                    const femaleDisEl = document.getElementById('statFemaleDisabled');
                    const badgeFemale = document.getElementById('badgeFemaleTersedia');
                    const barFemaleOcc = document.getElementById('barFemaleOccupied');
                    const barFemaleDis = document.getElementById('barFemaleDisabled');

                    if (femaleAvailEl) femaleAvailEl.textContent = sum.available_female;
                    if (femaleOccEl) femaleOccEl.textContent = `${sum.occupied_female} Terisi`;
                    if (femaleDisEl) femaleDisEl.textContent = `${sum.disabled_female} Dikunci (Maintenance)`;
                    if (badgeFemale) badgeFemale.textContent = `${sum.available_female} / 100 Tersedia`;
                    if (barFemaleOcc) {
                        const pct = sum.total_female > 0 ? (sum.occupied_female / sum.total_female) * 100 : 0;
                        barFemaleOcc.style.width = pct + '%';
                        barFemaleOcc.setAttribute('title', `Terisi: ${sum.occupied_female}`);
                    }
                    if (barFemaleDis) {
                        const pct = sum.total_female > 0 ? (sum.disabled_female / sum.total_female) * 100 : 0;
                        barFemaleDis.style.width = pct + '%';
                        barFemaleDis.setAttribute('title', `Dikunci: ${sum.disabled_female}`);
                    }

                    // Update grid boxes (Laki-laki & Perempuan)
                    const updateGrid = (items) => {
                        items.forEach(item => {
                            const box = document.getElementById(`locker-cell-${item.id}`);
                            if (!box) return;

                            const isModalOpenForThis = !modal.classList.contains('hidden') && currentLockerId === String(item.id);
                            
                            box.setAttribute('data-status', item.status);
                            box.setAttribute('data-is-active', item.is_active ? '1' : '0');
                            box.setAttribute('data-karyawan', item.karyawan_nama || '');
                            box.setAttribute('data-nik', item.karyawan_nik || '');
                            box.setAttribute('data-alokasi-id', item.alokasi_id || '');

                            box.className = `loker-box status-${item.status} locker-clickable text-xs`;
                            box.innerHTML = `<span>${item.nomor}</span>` + (!item.is_active ? '<span class="text-[9px] leading-none">🔒</span>' : '');

                            // Jika modal sedang terbuka untuk loker ini, perbarui status di modal langsung
                            if (isModalOpenForThis) {
                                openModalForLocker(box);
                            }
                        });
                    };

                    updateGrid(data.male);
                    updateGrid(data.female);

                } catch (e) {
                    // Abaikan kesalahan koneksi sementara saat polling
                }
            }

            // Jalankan polling auto-sync real-time setiap 5 detik
            setInterval(syncLockerStatus, 5000);
        });
    </script>
@endpush
