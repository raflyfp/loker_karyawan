@extends('layouts.admin')

@section('title', 'Master Data Loker')

@section('content')
<div class="space-y-5">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Master Data Loker</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data 100 loker Laki-laki dan 100 loker Perempuan, nomor urut, serta status pemeliharaan.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="openCreateModalBtn"
                class="px-3.5 py-2 text-xs font-bold text-white bg-[#d92534] hover:bg-[#b91c2b] rounded-md transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Loker</span>
            </button>
        </div>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
        <div class="bg-white p-3 rounded-lg border border-slate-200">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Loker</span>
            <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total']) }}</span>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200">
            <span class="text-[11px] font-semibold text-[#0060b2] uppercase tracking-wider block">Kelompok L</span>
            <span class="text-xl font-bold text-[#0060b2]">{{ number_format($stats['laki']) }}</span>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200">
            <span class="text-[11px] font-semibold text-[#d92534] uppercase tracking-wider block">Kelompok P</span>
            <span class="text-xl font-bold text-[#d92534]">{{ number_format($stats['perempuan']) }}</span>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200">
            <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">Tersedia</span>
            <span class="text-xl font-bold text-emerald-700">{{ number_format($stats['tersedia']) }}</span>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200">
            <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider block">Terisi</span>
            <span class="text-xl font-bold text-rose-700">{{ number_format($stats['terisi']) }}</span>
        </div>
        <div class="bg-white p-3 rounded-lg border border-slate-200">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Dikunci / Rusak</span>
            <span class="text-xl font-bold text-slate-500">{{ number_format($stats['rusak']) }}</span>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-3.5 rounded-lg border border-slate-200 shadow-2xs">
        <form method="GET" action="{{ route('admin.loker.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode loker (L-12) atau nomor urut..."
                    class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
            </div>

            <div class="sm:col-span-3">
                <select name="gender" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    <option value="">Semua Kelompok</option>
                    <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Tersedia</option>
                    <option value="occupied" {{ request('status') === 'occupied' ? 'selected' : '' }}>Terisi</option>
                    <option value="disabled" {{ request('status') === 'disabled' ? 'selected' : '' }}>Dikunci / Rusak</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex gap-1.5">
                <button type="submit" class="flex-1 py-1.5 px-3 text-xs font-semibold bg-slate-800 text-white rounded hover:bg-slate-700 transition">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'gender', 'status']))
                    <a href="{{ route('admin.loker.index') }}" class="py-1.5 px-2.5 text-xs font-semibold bg-slate-100 text-slate-600 rounded hover:bg-slate-200 transition" title="Reset Filter">
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
                    <tr class="bg-slate-50 text-slate-600 border-b border-slate-200 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Kode Loker</th>
                        <th class="py-3 px-4">Nomor Tampilan</th>
                        <th class="py-3 px-4">Kelompok</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Pengguna Saat Ini</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($lokers as $item)
                        @php
                            $isOccupied = $item->alokasiAktif !== null;
                            $karyawan = $isOccupied ? $item->alokasiAktif->karyawan : null;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $item->kode_loker }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900 whitespace-nowrap text-sm">
                                {{ $item->nomor_urut }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($item->gender_group === 'L')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-[#0060b2]">
                                        ♂ Laki-laki
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-[#d92534]">
                                        ♀ Perempuan
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if (!$item->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-200 text-slate-600 border border-slate-300">
                                        🔒 Terkunci / Rusak
                                    </span>
                                @elseif ($isOccupied)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-800 border border-rose-200">
                                        ● Terisi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        ✓ Tersedia
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($isOccupied && $karyawan)
                                    <div class="font-semibold text-slate-900">{{ $karyawan->nama }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $karyawan->nik }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">

                                    <!-- Quick Toggle Lock Button -->
                                    <form method="POST" action="{{ route('admin.loker.toggle-active', $item) }}" class="inline m-0">
                                        @csrf
                                        <button type="submit"
                                            class="px-2 py-1 text-[11px] font-semibold rounded transition {{ $item->is_active ? 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200' }}"
                                            title="{{ $item->is_active ? 'Kunci loker untuk pemeliharaan' : 'Buka kunci loker' }}">
                                            {{ $item->is_active ? 'Kunci' : 'Buka Kunci' }}
                                        </button>
                                    </form>

                                    <!-- Edit Button -->
                                    <button type="button"
                                        class="editLokerBtn px-2 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition"
                                        data-id="{{ $item->id }}"
                                        data-kode="{{ $item->kode_loker }}"
                                        data-nomor="{{ $item->nomor_urut }}"
                                        data-gender="{{ $item->gender_group }}"
                                        data-active="{{ $item->is_active ? '1' : '0' }}">
                                        Edit
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.loker.destroy', $item) }}" class="inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus loker {{ $item->kode_loker }}?');"
                                            {{ $isOccupied ? 'disabled' : '' }}
                                            class="px-2 py-1 text-[11px] font-semibold rounded transition {{ $isOccupied ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400' : 'text-rose-700 bg-rose-50 hover:bg-rose-100' }}">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada data loker yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lokers->hasPages())
            <div class="p-3 border-t border-slate-200 bg-slate-50">
                {{ $lokers->links() }}
            </div>
        @endif
    </div>

</div>

<!-- ============================================== -->
<!-- MODAL: TAMBAH LOKER                            -->
<!-- ============================================== -->
<div id="createModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-lg border border-slate-200 max-w-sm w-full p-5 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Tambah Loker Baru</h3>
            <button type="button" id="closeCreateModalBtn" class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.loker.store') }}" class="mt-4 space-y-3">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Loker <span class="text-rose-500">*</span></label>
                <input type="text" name="kode_loker" required placeholder="Contoh: L-101 atau P-101"
                    class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534] font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Urut Tampilan <span class="text-rose-500">*</span></label>
                <input type="number" name="nomor_urut" required min="1" placeholder="Contoh: 101"
                    class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kelompok Gender <span class="text-rose-500">*</span></label>
                <select name="gender_group" required class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    <option value="L">Laki-laki (Loker Atas)</option>
                    <option value="P">Perempuan (Loker Bawah)</option>
                </select>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-[#d92534] focus:ring-[#d92534]">
                    <span class="font-medium">Loker Aktif & Dapat Digunakan</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" id="cancelCreateModalBtn" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded">
                    Batal
                </button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-[#d92534] hover:bg-[#b91c2b] rounded shadow-xs">
                    Simpan Loker
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================== -->
<!-- MODAL: EDIT LOKER                              -->
<!-- ============================================== -->
<div id="editModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-lg border border-slate-200 max-w-sm w-full p-5 shadow-xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Edit Data Loker</h3>
            <button type="button" id="closeEditModalBtn" class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
        </div>

        <form id="editForm" method="POST" action="" class="mt-4 space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Loker <span class="text-rose-500">*</span></label>
                <input type="text" id="editKode" name="kode_loker" required
                    class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534] font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Urut Tampilan <span class="text-rose-500">*</span></label>
                <input type="number" id="editNomor" name="nomor_urut" required min="1"
                    class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kelompok Gender <span class="text-rose-500">*</span></label>
                <select id="editGender" name="gender_group" required class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    <option value="L">Laki-laki (Loker Atas)</option>
                    <option value="P">Perempuan (Loker Bawah)</option>
                </select>
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" id="editActive" name="is_active" value="1" class="rounded border-slate-300 text-[#d92534] focus:ring-[#d92534]">
                    <span class="font-medium">Loker Aktif & Dapat Digunakan</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" id="cancelEditModalBtn" class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded">
                    Batal
                </button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-[#0060b2] hover:bg-[#004d8f] rounded shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Create Modal Logic
    const createModal = document.getElementById('createModal');
    document.getElementById('openCreateModalBtn')?.addEventListener('click', () => createModal.classList.remove('hidden'));
    document.getElementById('closeCreateModalBtn')?.addEventListener('click', () => createModal.classList.add('hidden'));
    document.getElementById('cancelCreateModalBtn')?.addEventListener('click', () => createModal.classList.add('hidden'));

    // Edit Modal Logic
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const editKode = document.getElementById('editKode');
    const editNomor = document.getElementById('editNomor');
    const editGender = document.getElementById('editGender');
    const editActive = document.getElementById('editActive');

    document.querySelectorAll('.editLokerBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            editForm.action = `{{ url('admin/loker') }}/${id}`;
            editKode.value = btn.getAttribute('data-kode');
            editNomor.value = btn.getAttribute('data-nomor');
            editGender.value = btn.getAttribute('data-gender');
            editActive.checked = btn.getAttribute('data-active') === '1';

            editModal.classList.remove('hidden');
        });
    });

    document.getElementById('closeEditModalBtn')?.addEventListener('click', () => editModal.classList.add('hidden'));
    document.getElementById('cancelEditModalBtn')?.addEventListener('click', () => editModal.classList.add('hidden'));
</script>
@endpush
@endsection
