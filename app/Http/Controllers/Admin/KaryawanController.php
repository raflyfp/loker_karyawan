<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Services\KaryawanSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    /**
     * Tampilkan daftar master karyawan dengan pencarian & filter.
     */
    public function index(Request $request): View
    {
        $query = Karyawan::with('alokasiAktif.loker');

        // Pencarian teks (NIK, Nama, Divisi, Jabatan)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('divisi', 'like', "%{$search}%")
                    ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        // Filter Gender
        if ($gender = $request->input('gender')) {
            if ($gender === 'null') {
                $query->whereNull('gender');
            } elseif (in_array($gender, ['L', 'P'])) {
                $query->where('gender', $gender);
            }
        }

        // Filter Status Aktif
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $karyawans = $query->orderBy('nama', 'asc')->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Karyawan::count(),
            'laki' => Karyawan::where('gender', 'L')->count(),
            'perempuan' => Karyawan::where('gender', 'P')->count(),
            'belum_diatur' => Karyawan::whereNull('gender')->count(),
        ];

        return view('admin.karyawan.index', compact('karyawans', 'stats'));
    }

    /**
     * Simpan data karyawan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:50', 'unique:karyawan,nik'],
            'nama' => ['required', 'string', 'max:150'],
            'divisi' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nik.required' => 'NIK / Barcode wajib diisi.',
            'nik.unique' => 'NIK / Barcode ini sudah terdaftar.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'gender.in' => 'Gender harus berupa L (Laki-laki) atau P (Perempuan).',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Karyawan::create($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', "Karyawan '{$validated['nama']}' ({$validated['nik']}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data karyawan.
     */
    public function update(Request $request, Karyawan $karyawan): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:50', Rule::unique('karyawan', 'nik')->ignore($karyawan->id)],
            'nama' => ['required', 'string', 'max:150'],
            'divisi' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'nik.required' => 'NIK / Barcode wajib diisi.',
            'nik.unique' => 'NIK / Barcode ini sudah digunakan oleh karyawan lain.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'gender.in' => 'Gender harus berupa L (Laki-laki) atau P (Perempuan).',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $karyawan->update($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', "Data karyawan '{$karyawan->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus data karyawan.
     */
    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        if ($karyawan->alokasiAktif()->exists()) {
            return redirect()->route('admin.karyawan.index')
                ->with('error', "Karyawan '{$karyawan->nama}' sedang aktif menggunakan loker. Harap lepaskan loker terlebih dahulu sebelum menghapus.");
        }

        $nama = $karyawan->nama;
        $karyawan->delete();

        return redirect()->route('admin.karyawan.index')
            ->with('success', "Karyawan '{$nama}' berhasil dihapus.");
    }

    /**
     * Sinkronkan data master karyawan dari API SNA Medika.
     */
    public function sync(KaryawanSyncService $syncService): RedirectResponse
    {
        try {
            $result = $syncService->syncFromApi();

            return redirect()->route('admin.karyawan.index')
                ->with('success', "Sinkronisasi berhasil! {$result['total_synced']} data karyawan diperbarui dari total {$result['total_fetched']} data API SNA Medika.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.karyawan.index')
                ->with('error', 'Gagal melakukan sinkronisasi API: ' . $e->getMessage());
        }
    }
}
