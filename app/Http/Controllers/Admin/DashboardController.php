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

        $totalKaryawan = Karyawan::count();
        $karyawanAktif = Karyawan::where('is_active', true)->count();
        $karyawanGenderNull = Karyawan::whereNull('gender')->count();

        $recentAllocations = AlokasiLoker::with(['karyawan', 'loker'])
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalLoker',
            'maleTotal',
            'femaleTotal',
            'activeAllocations',
            'maleOccupied',
            'femaleOccupied',
            'maleDisabled',
            'femaleDisabled',
            'maleAvailable',
            'femaleAvailable',
            'totalKaryawan',
            'karyawanAktif',
            'karyawanGenderNull',
            'recentAllocations'
        ));
    }
}
