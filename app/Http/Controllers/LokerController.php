<?php

namespace App\Http\Controllers;

use App\Models\Loker;
use Illuminate\View\View;

class LokerController extends Controller
{
    /**
     * Tampilkan halaman utama terminal loker mandiri karyawan.
     */
    public function index(): View
    {
        $maleLockers = Loker::with('alokasiAktif.karyawan:id,nama')
            ->where('gender_group', 'L')
            ->orderBy('nomor_urut')
            ->get();

        $femaleLockers = Loker::with('alokasiAktif.karyawan:id,nama')
            ->where('gender_group', 'P')
            ->orderBy('nomor_urut')
            ->get();

        $maleAvailable = $maleLockers->where('is_active', true)->filter(fn($l) => $l->alokasiAktif === null)->count();
        $femaleAvailable = $femaleLockers->where('is_active', true)->filter(fn($l) => $l->alokasiAktif === null)->count();

        return view('loker', [
            'maleLockers' => $maleLockers,
            'femaleLockers' => $femaleLockers,
            'maleAvailable' => $maleAvailable,
            'femaleAvailable' => $femaleAvailable,
        ]);
    }
}
