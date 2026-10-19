@extends('layouts.admin')

@section('title', 'Laporan Pemakaian Loker')
@section('page_title', 'Laporan Riwayat Pemakaian Loker')

@section('content')
    <div class="space-y-6">

        <!-- Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Laporan Pemakaian Loker</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Analisis riwayat alokasi peminjaman loker karyawan dan
                    unduh rekap data Excel (.xlsx).</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Export Excel Button -->
                <a href="{{ route('admin.report.export', request()->all()) }}"
                    class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition flex items-center gap-2 shadow-2xs">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Export ke Excel (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- METRICS CARDS -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Transaksi</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ number_format($totalUsage) }}</span>
                    <span class="text-xs text-slate-400">kali dipakai</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Sedang Aktif</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span
                        class="text-2xl sm:text-3xl font-extrabold text-emerald-700">{{ number_format($activeUsage) }}</span>
                    <span class="text-xs text-slate-400">loker</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block">Selesai
                    Dikembalikan</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span
                        class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ number_format($completedUsage) }}</span>
                    <span class="text-xs text-slate-400">transaksi</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <span class="text-[11px] font-bold text-[#0060b2] uppercase tracking-wider block">Rata-rata Durasi</span>
                <div class="mt-2 flex items-baseline gap-2">
                    <span
                        class="text-lg sm:text-xl font-extrabold text-[#0060b2] truncate">{{ $avgDurationFormatted }}</span>
                </div>
            </div>
        </div>

        <!-- FILTER & SEARCH PANEL -->
        <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-2xs">
            <form method="GET" action="{{ route('admin.report.index') }}" class="space-y-4">

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">

                    <!-- Tanggal Mulai -->
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    </div>

                    <!-- Tanggal Selesai -->
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    </div>

                    <!-- Kelompok Gender Loker -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kelompok Loker</label>
                        <select name="gender"
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                            <option value="">Semua Loker</option>
                            <option value="L" {{ $gender === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ $gender === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        </select>
                    </div>

                    <!-- Status Alokasi -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                            <option value="">Semua Status</option>
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Sedang Aktif</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="sm:col-span-2 flex gap-1.5">
                        <button type="submit"
                            class="flex-1 py-2 px-3 text-xs font-bold bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition">
                            Terapkan
                        </button>
                        @if (request()->hasAny(['start_date', 'end_date', 'gender', 'status', 'search']))
                            <a href="{{ route('admin.report.index') }}"
                                class="py-2 px-3 text-xs font-bold bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition"
                                title="Reset Filter">
                                ✕
                            </a>
                        @endif
                    </div>

                </div>

                <!-- Row 2: Search Input -->
                <div class="pt-2 border-t border-slate-100 flex items-center gap-3">
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari nama karyawan, NIK, atau nomor loker (cth: Budi, L-05)..."
                            class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-300 rounded-lg focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

            </form>
        </div>



        <!-- DATA TABLE -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Rincian Transaksi Pemakaian Loker</h2>
                    <p class="text-xs text-slate-500">Menampilkan hasil data sesuai filter yang dipilih.</p>
                </div>
                <div class="text-xs text-slate-500">
                    Total <strong>{{ $alokasis->total() }}</strong> catatan data ditemukan
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-600 border-b border-slate-200 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">No</th>
                            <th class="py-3 px-4">Tanggal Pinjam</th>
                            <th class="py-3 px-4">Jam Masuk</th>
                            <th class="py-3 px-4">Jam Keluar</th>
                            <th class="py-3 px-4">Durasi</th>
                            <th class="py-3 px-4">No. Loker</th>
                            <th class="py-3 px-4">Karyawan</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($alokasis as $index => $item)
                            @php
                                $durasiStr = '-';
                                if ($item->waktu_masuk) {
                                    if ($item->waktu_keluar) {
                                        $diffM = $item->waktu_masuk->diffInMinutes($item->waktu_keluar);
                                        $durasiStr = floor($diffM / 60) . 'j ' . $diffM % 60 . 'm';
                                    } elseif ($item->status === 'active') {
                                        $diffM = $item->waktu_masuk->diffInMinutes(now());
                                        $durasiStr = floor($diffM / 60) . 'j ' . $diffM % 60 . 'm (Berjalan)';
                                    }
                                }
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-slate-400 font-mono">
                                    {{ $alokasis->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 whitespace-nowrap font-medium">
                                    {{ $item->waktu_masuk ? $item->waktu_masuk->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap font-mono">
                                    {{ $item->waktu_masuk ? $item->waktu_masuk->format('H:i:s') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 whitespace-nowrap font-mono">
                                    {{ $item->waktu_keluar ? $item->waktu_keluar->format('H:i:s') : '-' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap font-medium text-slate-800">
                                    {{ $durasiStr }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-bold text-slate-900">{{ $item->loker->kode_loker ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-500 block">
                                        {{ ($item->loker->gender_group ?? '') === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $item->karyawan->nama ?? 'Tidak Diketahui' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $item->karyawan->nik ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($item->status === 'active')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                            Aktif
                                        </span>
                                    @elseif ($item->status === 'completed')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                            Selesai
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-700">
                                            {{ $item->status }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Tidak ada data riwayat pemakaian loker yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($alokasis->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $alokasis->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
