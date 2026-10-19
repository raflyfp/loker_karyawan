@extends('layouts.admin')

@section('title', 'Data Alokasi & Riwayat Penggunaan')
@section('page_title', 'Data Alokasi & Riwayat Loker')

@section('content')
    <div class="space-y-5">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Data Alokasi & Riwayat Transaksi
                </h1>
                {{-- <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pemantauan riwayat penggunaan loker, waktu masuk/keluar, dan checkout paksa jika kartu hilang.</p> --}}
            </div>
        </div>

        <!-- Quick Stats Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Transaksi</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total']) }}</span>
            </div>
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">Sedang Aktif</span>
                <span class="text-xl font-bold text-emerald-700">{{ number_format($stats['aktif']) }}</span>
            </div>
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-slate-600 uppercase tracking-wider block">Selesai /
                    Dikembalikan</span>
                <span class="text-xl font-bold text-slate-700">{{ number_format($stats['selesai']) }}</span>
            </div>
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider block">Dibatalkan</span>
                <span class="text-xl font-bold text-rose-700">{{ number_format($stats['dibatalkan']) }}</span>
            </div>
        </div>

        <!-- Filters & Search Bar -->
        <div class="bg-white p-3.5 rounded-lg border border-slate-200 shadow-2xs">
            <form method="GET" action="{{ route('admin.alokasi.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama karyawan, NIK barcode, kode loker..."
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                </div>

                <div class="sm:col-span-3">
                    <select name="status"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif Digunakan
                        </option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai /
                            Dikembalikan</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan
                        </option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <input type="date" name="date" value="{{ request('date') }}"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                </div>

                <div class="sm:col-span-2 flex gap-1.5">
                    <button type="submit"
                        class="flex-1 py-1.5 px-3 text-xs font-semibold bg-slate-800 text-white rounded hover:bg-slate-700 transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'status', 'date']))
                        <a href="{{ route('admin.alokasi.index') }}"
                            class="py-1.5 px-2.5 text-xs font-semibold bg-slate-100 text-slate-600 rounded hover:bg-slate-200 transition"
                            title="Reset Filter">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-lg border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr
                            class="bg-slate-50 text-slate-600 border-b border-slate-200 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Loker</th>
                            <th class="py-3 px-4">Karyawan</th>
                            <th class="py-3 px-4">Waktu Masuk (Alokasi)</th>
                            <th class="py-3 px-4">Waktu Keluar (Pelepasan)</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($alokasis as $item)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono text-slate-400">
                                    #{{ $item->id }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span
                                        class="font-bold text-slate-900 text-sm">{{ $item->loker->kode_loker ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-500 block">
                                        {{ $item->loker && $item->loker->gender_group === 'L' ? '♂ Laki-laki' : '♀ Perempuan' }}
                                        (No. {{ $item->loker->nomor_urut ?? '-' }})
                                    </span>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900">
                                        {{ $item->karyawan->nama ?? 'Karyawan Dihapus' }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $item->karyawan->nik ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
                                    {{ $item->waktu_masuk ? date('d/m/Y H:i:s', strtotime($item->waktu_masuk)) : '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 whitespace-nowrap">
                                    {{ $item->waktu_keluar ? date('d/m/Y H:i:s', strtotime($item->waktu_keluar)) : '-' }}
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($item->status === 'active')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            ● Sedang Aktif
                                        </span>
                                    @elseif ($item->status === 'completed')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                            ✓ Selesai
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-700">
                                            {{ $item->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    @if ($item->status === 'active')
                                        <form method="POST" action="{{ route('admin.alokasi.force-release', $item) }}"
                                            class="inline m-0">
                                            @csrf
                                            <button type="submit"
                                                onclick="return confirm('Paksa pelepasan loker {{ $item->loker->kode_loker ?? '' }} untuk karyawan {{ $item->karyawan->nama ?? '' }}? Loker akan kembali berstatus tersedia.');"
                                                class="px-2.5 py-1 text-[11px] font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded transition shadow-2xs"
                                                title="Checkout / Lepaskan loker jika karyawan lupa tap out atau kartu hilang">
                                                Paksa Lepas
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Tidak ada data alokasi yang cocok dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($alokasis->hasPages())
                <div class="p-3 border-t border-slate-200 bg-slate-50">
                    {{ $alokasis->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
