<?php

namespace App\Services;

use App\Models\AlokasiLoker;
use App\Models\Karyawan;
use App\Models\Loker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class LokerAllocationService
{
    /**
     * Memproses scan barcode karyawan (alokasi pertama atau pelepasan kedua).
     *
     * @param string $barcode NKK / NIK karyawan
     * @param string|null $genderChoice Pilihan gender jika belum terdaftar ('L' atau 'P')
     * @param string|null $idempotencyKey Key opsional untuk deduplikasi request jaringan
     * @return array
     * @throws ValidationException
     */
    public function processScan(string $barcode, ?string $genderChoice = null, ?string $idempotencyKey = null): array
    {
        $cleanBarcode = trim($barcode);
        if ($cleanBarcode === '') {
            throw ValidationException::withMessages([
                'barcode' => 'Barcode tidak boleh kosong.',
            ]);
        }

        // 1. Server-side Rate Limiting / Debounce lock per NKK (1.5 detik)
        // Mencegah scanner keyboard fisik mengirim Enter ganda / kamera membaca frame bertubi-tubi
        $lockKey = 'scan_lock_' . md5($cleanBarcode);
        $lock = Cache::lock($lockKey, 2);

        if (!$lock->get()) {
            throw ValidationException::withMessages([
                'barcode' => 'Pemindaian sedang diproses. Mohon tunggu sejenak.',
            ]);
        }

        try {
            return DB::transaction(function () use ($cleanBarcode, $genderChoice, $idempotencyKey) {
                // 2. Kunci baris karyawan untuk menghindari race condition
                $karyawan = Karyawan::where('nik', $cleanBarcode)
                    ->lockForUpdate()
                    ->first();

                if (!$karyawan) {
                    throw ValidationException::withMessages([
                        'barcode' => 'Karyawan dengan NKK / Barcode ini tidak terdaftar.',
                    ]);
                }

                if (!$karyawan->is_active) {
                    throw ValidationException::withMessages([
                        'barcode' => "Status karyawan '{$karyawan->nama}' tidak aktif. Hubungi HRD.",
                    ]);
                }

                // 3. Periksa apakah sudah ada alokasi aktif (Scan Kedua -> Pelepasan)
                $alokasiAktif = AlokasiLoker::where('karyawan_id', $karyawan->id)
                    ->where('status', 'active')
                    ->with('loker')
                    ->lockForUpdate()
                    ->first();

                if ($alokasiAktif) {
                    return $this->releaseLocker($alokasiAktif, $karyawan);
                }

                // 4. Jika belum ada alokasi aktif (Scan Pertama -> Alokasi)
                return $this->allocateLocker($karyawan, $genderChoice, $idempotencyKey);
            });
        } finally {
            $lock->release();
        }
    }

    /**
     * Melepaskan loker yang sedang aktif dimiliki karyawan.
     */
    protected function releaseLocker(AlokasiLoker $alokasi, Karyawan $karyawan): array
    {
        $loker = $alokasi->loker;

        $alokasi->update([
            'status' => 'completed',
            'waktu_keluar' => now(),
        ]);

        $speechNomor = $this->terbilang($loker->nomor_urut);

        return [
            'action' => 'released',
            'karyawan' => [
                'id' => $karyawan->id,
                'nik' => $karyawan->nik,
                'nama' => $karyawan->nama,
            ],
            'loker' => [
                'id' => $loker->id,
                'kode_loker' => $loker->kode_loker,
                'nomor_urut' => $loker->nomor_urut,
                'gender_group' => $loker->gender_group,
            ],
            'message' => "Loker nomor {$loker->nomor_urut} ({$loker->kode_loker}) berhasil dikembalikan.",
            'speech_text' => 'Terima kasih',
        ];
    }

    /**
     * Mengalokasikan loker kosong pertama sesuai gender karyawan.
     */
    protected function allocateLocker(Karyawan $karyawan, ?string $genderChoice, ?string $idempotencyKey): array
    {
        // Tangani jika gender belum ada
        if (empty($karyawan->gender)) {
            if ($genderChoice && in_array(strtoupper($genderChoice), ['L', 'P'])) {
                $karyawan->gender = strtoupper($genderChoice);
                $karyawan->save();
            } else {
                // Tampilkan opsi pilih gender di kiosk jika belum terpetakan
                return [
                    'action' => 'requires_gender',
                    'karyawan' => [
                        'id' => $karyawan->id,
                        'nik' => $karyawan->nik,
                        'nama' => $karyawan->nama,
                    ],
                    'message' => "Data kelompok belum ditentukan. Silakan pilih Laki-laki atau Perempuan untuk {$karyawan->nama}.",
                    'speech_text' => "Silakan pilih kelompok loker Anda.",
                ];
            }
        }

        $gender = $karyawan->gender;
        $genderLabel = $gender === 'L' ? 'Laki-laki' : 'Perempuan';

        // Cari loker kosong pertama dengan nomor urut terkecil yang aktif
        $loker = Loker::where('gender_group', $gender)
            ->where('is_active', true)
            ->whereDoesntHave('alokasiAktif')
            ->orderBy('nomor_urut', 'asc')
            ->lockForUpdate()
            ->first();

        if (!$loker) {
            throw ValidationException::withMessages([
                'barcode' => "Loker untuk kelompok {$genderLabel} saat ini sedang penuh.",
            ]);
        }

        $alokasi = AlokasiLoker::create([
            'karyawan_id' => $karyawan->id,
            'loker_id' => $loker->id,
            'waktu_masuk' => now(),
            'status' => 'active',
            'idempotency_key' => $idempotencyKey,
        ]);

        $speechNomor = $this->terbilang($loker->nomor_urut);

        return [
            'action' => 'allocated',
            'karyawan' => [
                'id' => $karyawan->id,
                'nik' => $karyawan->nik,
                'nama' => $karyawan->nama,
            ],
            'loker' => [
                'id' => $loker->id,
                'kode_loker' => $loker->kode_loker,
                'nomor_urut' => $loker->nomor_urut,
                'gender_group' => $loker->gender_group,
            ],
            'message' => "Selamat datang, {$karyawan->nama}. Silakan gunakan loker nomor {$loker->nomor_urut}.",
            'speech_text' => "Silakan nomor loker {$speechNomor}.",
        ];
    }

    /**
     * Konversi angka ke kata dalam bahasa Indonesia untuk Text-To-Speech yang alami.
     */
    public function terbilang(int $nilai): string
    {
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($nilai < 12) {
            return $huruf[$nilai];
        } elseif ($nilai < 20) {
            return $this->terbilang($nilai - 10) . ' belas';
        } elseif ($nilai < 100) {
            $sisa = $nilai % 10;
            $puluh = (int) ($nilai / 10);
            return $huruf[$puluh] . ' puluh' . ($sisa > 0 ? ' ' . $huruf[$sisa] : '');
        } elseif ($nilai == 100) {
            return 'seratus';
        }

        return (string) $nilai;
    }
}
