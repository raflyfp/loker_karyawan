<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlokasiLoker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlokasiController extends Controller
{
    /**
     * Tampilkan data alokasi dan riwayat penggunaan loker.
     */
    public function index(Request $request): View
    {
        $query = AlokasiLoker::with(['karyawan', 'loker']);

        // Filter status
        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'completed', 'cancelled'])) {
                $query->where('status', $status);
            }
        }

        // Pencarian teks (Nama Karyawan, NIK, Kode Loker)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('karyawan', function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                })->orWhereHas('loker', function ($sub) use ($search) {
                    $sub->where('kode_loker', 'like', "%{$search}%")
                        ->orWhere('nomor_urut', $search);
                });
            });
        }

        // Filter Tanggal
        if ($date = $request->input('date')) {
            $query->whereDate('waktu_masuk', $date);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $alokasis = $query->orderByDesc('id')->paginate($perPage)->withQueryString();

        $stats = [
            'total' => AlokasiLoker::count(),
            'aktif' => AlokasiLoker::where('status', 'active')->count(),
            'selesai' => AlokasiLoker::where('status', 'completed')->count(),
            'dibatalkan' => AlokasiLoker::where('status', 'cancelled')->count(),
        ];

        return view('admin.alokasi.index', compact('alokasis', 'stats'));
    }

    /**
     * Paksa pelepasan loker aktif (Emergency Release / Reset oleh Admin).
     */
    public function forceRelease(AlokasiLoker $alokasi): RedirectResponse
    {
        if ($alokasi->status !== 'active') {
            return redirect()->back()
                ->with('error', 'Alokasi loker ini sudah tidak aktif.');
        }

        $karyawanName = $alokasi->karyawan->nama ?? 'Karyawan';
        $lokerCode = $alokasi->loker->kode_loker ?? '-';

        $alokasi->update([
            'status' => 'completed',
            'waktu_keluar' => now(),
        ]);

        return redirect()->back()
            ->with('success', "Loker {$lokerCode} yang digunakan oleh {$karyawanName} berhasil dilepaskan (checkout paksa).");
    }
}
