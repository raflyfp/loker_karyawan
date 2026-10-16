<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LokerController extends Controller
{
    /**
     * Tampilkan daftar master loker dengan filter & pencarian.
     */
    public function index(Request $request): View
    {
        $query = Loker::with('alokasiAktif.karyawan');

        // Pencarian (Kode loker atau nomor urut)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_loker', 'like', "%{$search}%")
                    ->orWhere('nomor_urut', $search);
            });
        }

        // Filter Kelompok Gender
        if ($gender = $request->input('gender')) {
            if (in_array($gender, ['L', 'P'])) {
                $query->where('gender_group', $gender);
            }
        }

        // Filter Status
        if ($status = $request->input('status')) {
            if ($status === 'occupied') {
                $query->whereHas('alokasiAktif');
            } elseif ($status === 'available') {
                $query->where('is_active', true)->whereDoesntHave('alokasiAktif');
            } elseif ($status === 'disabled') {
                $query->where('is_active', false);
            }
        }

        $lokers = $query->orderBy('gender_group', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total' => Loker::count(),
            'laki' => Loker::where('gender_group', 'L')->count(),
            'perempuan' => Loker::where('gender_group', 'P')->count(),
            'terisi' => Loker::whereHas('alokasiAktif')->count(),
            'tersedia' => Loker::where('is_active', true)->whereDoesntHave('alokasiAktif')->count(),
            'rusak' => Loker::where('is_active', false)->count(),
        ];

        return view('admin.loker.index', compact('lokers', 'stats'));
    }

    /**
     * Simpan loker baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_loker' => ['required', 'string', 'max:20', 'unique:lokers,kode_loker'],
            'nomor_urut' => ['required', 'integer', 'min:1'],
            'gender_group' => ['required', Rule::in(['L', 'P'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode_loker.required' => 'Kode loker wajib diisi.',
            'kode_loker.unique' => 'Kode loker ini sudah ada.',
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'gender_group.required' => 'Kelompok gender wajib dipilih.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Loker::create($validated);

        return redirect()->route('admin.loker.index')
            ->with('success', "Loker '{$validated['kode_loker']}' berhasil ditambahkan.");
    }

    /**
     * Perbarui data master loker.
     */
    public function update(Request $request, Loker $loker): RedirectResponse
    {
        $validated = $request->validate([
            'kode_loker' => ['required', 'string', 'max:20', Rule::unique('lokers', 'kode_loker')->ignore($loker->id)],
            'nomor_urut' => ['required', 'integer', 'min:1'],
            'gender_group' => ['required', Rule::in(['L', 'P'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode_loker.required' => 'Kode loker wajib diisi.',
            'kode_loker.unique' => 'Kode loker ini sudah digunakan.',
            'nomor_urut.required' => 'Nomor urut wajib diisi.',
            'gender_group.required' => 'Kelompok gender wajib dipilih.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Jika dinonaktifkan tetapi sedang digunakan
        if (!$validated['is_active'] && $loker->alokasiAktif()->exists()) {
            return redirect()->route('admin.loker.index')
                ->with('error', "Loker {$loker->kode_loker} sedang aktif digunakan dan tidak dapat dinonaktifkan sampai dikembalikan.");
        }

        $loker->update($validated);

        return redirect()->route('admin.loker.index')
            ->with('success', "Loker {$loker->kode_loker} berhasil diperbarui.");
    }

    /**
     * Hapus loker dari database.
     */
    public function destroy(Loker $loker): RedirectResponse
    {
        if ($loker->alokasiAktif()->exists()) {
            return redirect()->route('admin.loker.index')
                ->with('error', "Loker {$loker->kode_loker} sedang aktif digunakan dan tidak dapat dihapus.");
        }

        $kode = $loker->kode_loker;
        $loker->delete();

        return redirect()->route('admin.loker.index')
            ->with('success', "Loker {$kode} berhasil dihapus.");
    }

    /**
     * Toggle status aktif / maintenance loker secara cepat.
     */
    public function toggleActive(Loker $loker): RedirectResponse
    {
        if ($loker->alokasiAktif()->exists() && $loker->is_active) {
            return redirect()->route('admin.loker.index')
                ->with('error', "Loker {$loker->kode_loker} sedang aktif digunakan dan tidak dapat dinonaktifkan.");
        }

        $loker->is_active = !$loker->is_active;
        $loker->save();

        $statusText = $loker->is_active ? 'diaktifkan' : 'dinonaktifkan/dikunci';

        return redirect()->back()
            ->with('success', "Loker {$loker->kode_loker} berhasil {$statusText}.");
    }
}
