<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlokasiLoker;
use App\Models\Karyawan;
use App\Models\Loker;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan ringkasan data dan metrik sistem loker.
     */
    public function index(): View
    {
        $totalLoker = Loker::count();
        $maleTotal = Loker::where('gender_group', 'L')->count();
        $femaleTotal = Loker::where('gender_group', 'P')->count();

        $activeAllocations = AlokasiLoker::where('status', 'active')->count();

        $maleOccupied = Loker::where('gender_group', 'L')
            ->whereHas('alokasiAktif')
            ->count();

        $femaleOccupied = Loker::where('gender_group', 'P')
            ->whereHas('alokasiAktif')
            ->count();

        $maleDisabled = Loker::where('gender_group', 'L')
            ->where('is_active', false)
            ->count();

        $femaleDisabled = Loker::where('gender_group', 'P')
            ->where('is_active', false)
            ->count();

        $maleAvailable = $maleTotal - $maleOccupied - $maleDisabled;
        $femaleAvailable = $femaleTotal - $femaleOccupied - $femaleDisabled;

        // Visualisasi Loker 1..100 Laki-laki & Perempuan
        $maleLockers = Loker::with('alokasiAktif.karyawan:id,nama,nik')
            ->where('gender_group', 'L')
            ->orderBy('nomor_urut')
            ->get();

        $femaleLockers = Loker::with('alokasiAktif.karyawan:id,nama,nik')
            ->where('gender_group', 'P')
            ->orderBy('nomor_urut')
            ->get();

        return view('admin.dashboard', compact(
            'totalLoker',
            'maleTotal',
            'femaleTotal',
            'maleOccupied',
            'femaleOccupied',
            'maleDisabled',
            'femaleDisabled',
            'maleAvailable',
            'femaleAvailable',
            'maleLockers',
            'femaleLockers'
        ));
    }
}
