@extends('layouts.admin')

@section('title', 'Master Data Karyawan')
@section('page_title', 'Master Data Karyawan')

@section('content')
    <div class="space-y-5">

        <!-- Header & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Master Data Karyawan</h1>
                {{-- <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Kelola data karyawan, pemetaan gender loker, dan sinkronisasi API SNA.</p> --}}
            </div>
            <div class="flex items-center gap-2">
                <!-- Sync API Button -->
                <form method="POST" action="{{ route('admin.karyawan.sync') }}" class="m-0">
                    @csrf
                    <button type="submit"
                        data-confirm="Mulai sinkronisasi master karyawan dengan API SNA Medika? Proses ini akan memperbarui data karyawan dari server pusat."
                        data-confirm-title="Sinkronisasi Data Karyawan"
                        data-confirm-text="Ya, Sinkronkan"
                        data-confirm-danger="false"
                        class="px-3.5 py-2 text-xs font-semibold text-[#0060b2] bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-md transition flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Sinkronkan API</span>
                    </button>
                </form>

                <!-- Tambah Karyawan Button -->
                <button type="button" id="openCreateModalBtn"
                    class="px-3.5 py-2 text-xs font-bold text-white bg-[#d92534] hover:bg-[#b91c2b] rounded-md transition flex items-center gap-1.5 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Tambah Karyawan</span>
                </button>
            </div>
        </div>

        <!-- Quick Stats Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Terdaftar</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($stats['total']) }}</span>
            </div>
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-[#0060b2] uppercase tracking-wider block">Laki-laki (L)</span>
                <span class="text-xl font-bold text-[#0060b2]">{{ number_format($stats['laki']) }}</span>
            </div>
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-[#d92534] uppercase tracking-wider block">Perempuan (P)</span>
                <span class="text-xl font-bold text-[#d92534]">{{ number_format($stats['perempuan']) }}</span>
            </div>
            <div class="bg-white p-3 rounded-lg border border-slate-200">
                <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block">Belum Diatur</span>
                <span class="text-xl font-bold text-amber-700">{{ number_format($stats['belum_diatur']) }}</span>
            </div>
        </div>

        <!-- Filters & Search Bar -->
        <div class="bg-white p-3.5 rounded-lg border border-slate-200 shadow-2xs">
            <form method="GET" action="{{ route('admin.karyawan.index') }}"
                class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <div class="sm:col-span-5">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari NIK barcode, nama, divisi, jabatan..."
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                </div>

                <div class="sm:col-span-3">
                    <select name="gender"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                        <option value="">Semua Kelompok Gender</option>
                        <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                        <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        <option value="null" {{ request('gender') === 'null' ? 'selected' : '' }}>Belum Diatur</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <select name="is_active"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="sm:col-span-2 flex gap-1.5">
                    <button type="submit"
                        class="flex-1 py-1.5 px-3 text-xs font-semibold bg-slate-800 text-white rounded hover:bg-slate-700 transition">
                        Filter
                    </button>
                    @if (request()->hasAny(['search', 'gender', 'is_active']))
                        <a href="{{ route('admin.karyawan.index') }}"
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
                            <th class="py-3 px-4">NIK (Barcode)</th>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Divisi & Jabatan</th>
                            <th class="py-3 px-4">Gender</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Loker Aktif</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($karyawans as $item)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                    {{ $item->nik }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $item->nama }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    <div>{{ $item->divisi ?? '-' }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $item->jabatan ?? '' }}</div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($item->gender === 'L')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-100 text-[#0060b2]">
                                            ♂ Laki-laki
                                        </span>
                                    @elseif ($item->gender === 'P')
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-[#d92534]">
                                            ♀ Perempuan
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-800">
                                            Belum Diatur
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($item->is_active)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($item->alokasiAktif && $item->alokasiAktif->loker)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            {{ $item->alokasiAktif->loker->kode_loker }}
                                            (#{{ $item->alokasiAktif->loker->nomor_urut }})
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                            class="editKaryawanBtn px-2 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded transition"
                                            data-id="{{ $item->id }}" data-nik="{{ $item->nik }}"
                                            data-nama="{{ $item->nama }}" data-divisi="{{ $item->divisi }}"
                                            data-jabatan="{{ $item->jabatan }}" data-gender="{{ $item->gender ?? '' }}"
                                            data-active="{{ $item->is_active ? '1' : '0' }}">
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('admin.karyawan.destroy', $item) }}"
                                            class="inline m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                data-confirm="Apakah Anda yakin ingin menghapus data karyawan {{ $item->nama }} (NIK: {{ $item->nik }})? Pastikan karyawan tidak sedang menggunakan loker."
                                                data-confirm-title="Hapus Data Karyawan"
                                                data-confirm-text="Ya, Hapus Data"
                                                class="px-2 py-1 text-[11px] font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 rounded transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    Tidak ada data karyawan yang cocok dengan kriteria pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($karyawans->total() > 0)
                <div class="p-3.5 border-t border-slate-200 bg-slate-50/80">
                    {{ $karyawans->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- ============================================== -->
    <!-- MODAL: TAMBAH KARYAWAN                         -->
    <!-- ============================================== -->
    <div id="createModal"
        class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg border border-slate-200 max-w-md w-full p-5 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Tambah Data Karyawan Baru</h3>
                <button type="button" id="closeCreateModalBtn"
                    class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.karyawan.store') }}" class="mt-4 space-y-3">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIK / Barcode Card <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="nik" required placeholder="Contoh: 12345678"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="nama" required placeholder="Contoh: Budi Santoso"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi</label>
                        <input type="text" name="divisi" placeholder="Contoh: Farmasi"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan</label>
                        <input type="text" name="jabatan" placeholder="Contoh: Staff"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kelompok Gender Loker</label>
                    <select name="gender"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                        <option value="">-- Pilih Gender --</option>
                        <option value="L">Laki-laki (Loker Atas 1..100)</option>
                        <option value="P">Perempuan (Loker Bawah 1..100)</option>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-0.5">Jika dikosongkan, karyawan akan diminta memilih kelompok
                        saat pertama kali scan kartu loker.</p>
                </div>

                <div class="pt-1">
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                            class="rounded border-slate-300 text-[#d92534] focus:ring-[#d92534]">
                        <span class="font-medium">Status Karyawan Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" id="cancelCreateModalBtn"
                        class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-1.5 text-xs font-bold text-white bg-[#d92534] hover:bg-[#b91c2b] rounded shadow-xs">
                        Simpan Karyawan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- MODAL: EDIT KARYAWAN                           -->
    <!-- ============================================== -->
    <div id="editModal"
        class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-lg border border-slate-200 max-w-md w-full p-5 shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Edit Data Karyawan</h3>
                <button type="button" id="closeEditModalBtn"
                    class="text-slate-400 hover:text-slate-700 text-lg leading-none">&times;</button>
            </div>

            <form id="editForm" method="POST" action="" class="mt-4 space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIK / Barcode Card <span
                            class="text-rose-500">*</span></label>
                    <input type="text" id="editNik" name="nik" required
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534] font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap <span
                            class="text-rose-500">*</span></label>
                    <input type="text" id="editNama" name="nama" required
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Divisi</label>
                        <input type="text" id="editDivisi" name="divisi"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jabatan</label>
                        <input type="text" id="editJabatan" name="jabatan"
                            class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kelompok Gender Loker</label>
                    <select id="editGender" name="gender"
                        class="w-full px-3 py-1.5 text-xs bg-slate-50 border border-slate-300 rounded focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#d92534]">
                        <option value="">-- Belum Diatur (Pilih saat Scan) --</option>
                        <option value="L">Laki-laki (Loker Atas 1..100)</option>
                        <option value="P">Perempuan (Loker Bawah 1..100)</option>
                    </select>
                </div>

                <div class="pt-1">
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" id="editActive" name="is_active" value="1"
                            class="rounded border-slate-300 text-[#d92534] focus:ring-[#d92534]">
                        <span class="font-medium">Status Karyawan Aktif</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" id="cancelEditModalBtn"
                        class="px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-1.5 text-xs font-bold text-white bg-[#0060b2] hover:bg-[#004d8f] rounded shadow-xs">
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
            document.getElementById('openCreateModalBtn')?.addEventListener('click', () => createModal.classList.remove(
                'hidden'));
            document.getElementById('closeCreateModalBtn')?.addEventListener('click', () => createModal.classList.add(
            'hidden'));
            document.getElementById('cancelCreateModalBtn')?.addEventListener('click', () => createModal.classList.add(
                'hidden'));

            // Edit Modal Logic
            const editModal = document.getElementById('editModal');
            const editForm = document.getElementById('editForm');
            const editNik = document.getElementById('editNik');
            const editNama = document.getElementById('editNama');
            const editDivisi = document.getElementById('editDivisi');
            const editJabatan = document.getElementById('editJabatan');
            const editGender = document.getElementById('editGender');
            const editActive = document.getElementById('editActive');

            document.querySelectorAll('.editKaryawanBtn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    editForm.action = `{{ url('admin/karyawan') }}/${id}`;
                    editNik.value = btn.getAttribute('data-nik');
                    editNama.value = btn.getAttribute('data-nama');
                    editDivisi.value = btn.getAttribute('data-divisi') || '';
                    editJabatan.value = btn.getAttribute('data-jabatan') || '';
                    editGender.value = btn.getAttribute('data-gender') || '';
                    editActive.checked = btn.getAttribute('data-active') === '1';

                    editModal.classList.remove('hidden');
                });
            });

            document.getElementById('closeEditModalBtn')?.addEventListener('click', () => editModal.classList.add('hidden'));
            document.getElementById('cancelEditModalBtn')?.addEventListener('click', () => editModal.classList.add('hidden'));
        </script>
    @endpush
@endsection
