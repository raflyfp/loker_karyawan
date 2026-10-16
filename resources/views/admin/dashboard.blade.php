@extends('layouts.admin')

@section('title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    <!-- Page Title & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard Overview</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pemantauan real-time kapasitas loker dan aktivitas karyawan SNA Medika.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.karyawan.index') }}" class="px-3 py-2 text-xs font-semibold bg-white border border-slate-300 rounded-md hover:bg-slate-50 text-slate-700 transition">
                Kelola Karyawan
            </a>
            <a href="{{ route('admin.loker.index') }}" class="px-3 py-2 text-xs font-semibold bg-white border border-slate-300 rounded-md hover:bg-slate-50 text-slate-700 transition">
                Kelola Loker
            </a>
            <a href="{{ route('loker.index') }}" target="_blank" class="px-3.5 py-2 text-xs font-bold text-white bg-[#d92534] hover:bg-[#b91c2b] rounded-md transition shadow-xs flex items-center gap-1.5">
                <span>Layar Kiosk</span>
                <span class="text-[10px]">↗</span>
            </a>
        </div>
    </div>

    <!-- METRIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card 1: Loker Laki-laki -->
        <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0060b2]">Loker Laki-laki</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                    Total {{ $maleTotal }}
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <span class="text-3xl font-extrabold text-slate-900">{{ $maleAvailable }}</span>
                    <span class="text-xs text-slate-500 font-medium ml-1">Tersedia</span>
                </div>
                <div class="text-right text-xs space-y-0.5">
                    <div class="text-rose-600 font-semibold">{{ $maleOccupied }} Terisi</div>
                    <div class="text-slate-400">{{ $maleDisabled }} Dikunci</div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3 overflow-hidden flex">
                @php
                    $maleOccPct = $maleTotal > 0 ? ($maleOccupied / $maleTotal) * 100 : 0;
                    $maleDisPct = $maleTotal > 0 ? ($maleDisabled / $maleTotal) * 100 : 0;
                @endphp
                <div class="bg-rose-500 h-1.5" style="width: {{ $maleOccPct }}%"></div>
                <div class="bg-slate-400 h-1.5" style="width: {{ $maleDisPct }}%"></div>
            </div>
        </div>

        <!-- Card 2: Loker Perempuan -->
        <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-[#d92534]">Loker Perempuan</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">
                    Total {{ $femaleTotal }}
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <span class="text-3xl font-extrabold text-slate-900">{{ $femaleAvailable }}</span>
                    <span class="text-xs text-slate-500 font-medium ml-1">Tersedia</span>
                </div>
                <div class="text-right text-xs space-y-0.5">
                    <div class="text-rose-600 font-semibold">{{ $femaleOccupied }} Terisi</div>
                    <div class="text-slate-400">{{ $femaleDisabled }} Dikunci</div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3 overflow-hidden flex">
                @php
                    $femOccPct = $femaleTotal > 0 ? ($femaleOccupied / $femaleTotal) * 100 : 0;
                    $femDisPct = $femaleTotal > 0 ? ($femaleDisabled / $femaleTotal) * 100 : 0;
                @endphp
                <div class="bg-rose-500 h-1.5" style="width: {{ $femOccPct }}%"></div>
                <div class="bg-slate-400 h-1.5" style="width: {{ $femDisPct }}%"></div>
            </div>
        </div>

        <!-- Card 3: Alokasi Aktif -->
        <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Alokasi Aktif</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <span class="text-3xl font-extrabold text-emerald-700">{{ $activeAllocations }}</span>
                    <span class="text-xs text-slate-500 font-medium ml-1">Sedang Digunakan</span>
                </div>
            </div>
            <div class="mt-3 text-xs text-slate-500">
                Loker aktif menampung barang karyawan saat ini.
            </div>
        </div>

        <!-- Card 4: Master Karyawan -->
        <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-2xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Master Karyawan</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $karyawanAktif }} Aktif
                </span>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <div>
                    <span class="text-3xl font-extrabold text-slate-900">{{ $totalKaryawan }}</span>
                    <span class="text-xs text-slate-500 font-medium ml-1">Total Karyawan</span>
                </div>
            </div>
            <div class="mt-3 text-xs text-slate-500 flex justify-between">
                <span>Belum Atur Gender:</span>
                <span class="font-bold text-amber-600">{{ $karyawanGenderNull }} orang</span>
            </div>
        </div>

    </div>

    <!-- QUICK ACTIONS & SHORTCUTS -->
    <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-2xs">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Tindakan Cepat Master Data</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('admin.karyawan.index', ['gender' => 'null']) }}"
                class="p-3 rounded-lg border border-slate-200 hover:border-amber-400 hover:bg-amber-50/40 transition flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm">
                    ?
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">Review Gender</div>
                    <div class="text-[11px] text-slate-500">{{ $karyawanGenderNull }} karyawan perlu diatur</div>
                </div>
            </a>

            <form method="POST" action="{{ route('admin.karyawan.sync') }}" class="m-0">
                @csrf
                <button type="submit" onclick="return confirm('Apakah Anda ingin menyinkronkan data seluruh karyawan dari API SNA Medika?');"
                    class="w-full text-left p-3 rounded-lg border border-slate-200 hover:border-blue-400 hover:bg-blue-50/40 transition flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-[#0060b2] flex items-center justify-center font-bold text-sm">
                        🔄
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Sync API SNA</div>
                        <div class="text-[11px] text-slate-500">Ambil data absen terbaru</div>
                    </div>
                </button>
            </form>

            <a href="{{ route('admin.loker.index', ['status' => 'disabled']) }}"
                class="p-3 rounded-lg border border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm">
                    🔒
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">Loker Terkunci</div>
                    <div class="text-[11px] text-slate-500">{{ $maleDisabled + $femaleDisabled }} loker maintenance</div>
                </div>
            </a>

            <a href="{{ route('admin.alokasi.index', ['status' => 'active']) }}"
                class="p-3 rounded-lg border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/40 transition flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">
                    ⚡
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">Monitoring Alokasi</div>
                    <div class="text-[11px] text-slate-500">{{ $activeAllocations }} sedang digunakan</div>
                </div>
            </a>
        </div>
    </div>

    <!-- RECENT ACTIVITY TABLE -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Aktivitas Alokasi & Pengembalian Terakhir</h2>
                <p class="text-xs text-slate-500">10 riwayat transaksi penggunaan loker terbaru</p>
            </div>
            <a href="{{ route('admin.alokasi.index') }}" class="text-xs font-semibold text-[#0060b2] hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 font-semibold uppercase tracking-wider">
                        <th class="py-2.5 px-4">Waktu</th>
                        <th class="py-2.5 px-4">Loker</th>
                        <th class="py-2.5 px-4">Karyawan</th>
                        <th class="py-2.5 px-4">Waktu Masuk</th>
                        <th class="py-2.5 px-4">Waktu Keluar</th>
                        <th class="py-2.5 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentAllocations as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-2.5 px-4 text-slate-500 whitespace-nowrap">
                                {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                            </td>
                            <td class="py-2.5 px-4 whitespace-nowrap">
                                <span class="font-bold text-slate-900">{{ $item->loker->kode_loker ?? '-' }}</span>
                                <span class="text-[11px] text-slate-500 ml-1">({{ $item->loker->gender_group === 'L' ? 'Laki-laki' : 'Perempuan' }})</span>
                            </td>
                            <td class="py-2.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-900">{{ $item->karyawan->nama ?? 'Tidak Diketahui' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $item->karyawan->nik ?? '-' }}</div>
                            </td>
                            <td class="py-2.5 px-4 text-slate-600 whitespace-nowrap">
                                {{ $item->waktu_masuk ? date('d/m/Y H:i:s', strtotime($item->waktu_masuk)) : '-' }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-600 whitespace-nowrap">
                                {{ $item->waktu_keluar ? date('d/m/Y H:i:s', strtotime($item->waktu_keluar)) : '-' }}
                            </td>
                            <td class="py-2.5 px-4 whitespace-nowrap">
                                @if ($item->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        Aktif
                                    </span>
                                @elseif ($item->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-700">
                                        {{ $item->status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Belum ada riwayat alokasi loker tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
