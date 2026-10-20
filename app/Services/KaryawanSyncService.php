<?php

namespace App\Services;

use App\Models\Karyawan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class KaryawanSyncService
{
    /**
     * Sinkronisasi data master karyawan dari API SNA Medika.
     *
     * @return array{total_fetched: int, total_synced: int}
     */
    public function syncFromApi(): array
    {
        $apiUrl = config('services.snam.api_url', 'https://www.snam110.dpdns.org/absen/api/karyawan');
        $timeout = (int) config('services.snam.timeout', 30);

        try {
            $response = Http::timeout($timeout)
                ->withoutVerifying()
                ->get($apiUrl);
        } catch (\Throwable $e) {
            Log::error('Gagal menghubungi API master karyawan: ' . $e->getMessage());
            throw new RuntimeException('Koneksi ke endpoint API SNA Medika gagal: ' . $e->getMessage(), 0, $e);
        }

        if (!$response->successful()) {
            Log::error("API master karyawan merespons status {$response->status()}");
            throw new RuntimeException("API SNA Medika mengembalikan status HTTP {$response->status()}");
        }

        $payload = $response->json();
        if (!is_array($payload) || !isset($payload['data']) || !is_array($payload['data'])) {
            Log::error('Format respons API karyawan tidak valid');
            throw new RuntimeException('Format respons API karyawan tidak sesuai struktur yang diharapkan.');
        }

        $items = $payload['data'];
        $totalFetched = count($items);
        $totalSynced = 0;
        $now = now();

        // Mengambil peta gender yang sudah ada agar sinkronisasi tidak menimpa pemetaan manual/lokal
        $existingGenders = Karyawan::pluck('gender', 'nik')->toArray();

        // Batch processing 200 items per chunk
        $chunks = array_chunk($items, 200);

        foreach ($chunks as $chunk) {
            $records = [];

            foreach ($chunk as $item) {
                if (empty($item['nik'])) {
                    continue;
                }

                $nik = trim((string) $item['nik']);
                $nama = trim((string) ($item['nama'] ?? ''));
                $nama = preg_replace('/^[^A-Za-z0-9]+/u', '', $nama) ?: $nama;

                // Jika di masa depan API menambahkan field jenis kelamin / gender, kita baca
                $apiGender = null;
                if (!empty($item['gender']) || !empty($item['jenis_kelamin'])) {
                    $rawG = strtoupper(trim((string) ($item['gender'] ?? $item['jenis_kelamin'])));
                    if (in_array($rawG, ['L', 'LAKI-LAKI', 'PRIA', 'MALE', 'M'])) {
                        $apiGender = 'L';
                    } elseif (in_array($rawG, ['P', 'PEREMPUAN', 'WANITA', 'FEMALE', 'F'])) {
                        $apiGender = 'P';
                    }
                }

                // Prioritaskan gender yang sudah ada di lokal jika ada, atau gunakan dari API jika ada
                $gender = $existingGenders[$nik] ?? $apiGender;

                // Ekstrak URL QR Code resmi dari respons API
                $qrCodeUrl = null;
                if (!empty($item['qr_code'])) {
                    if (is_array($item['qr_code'])) {
                        $qrCodeUrl = $item['qr_code']['image_url'] ?? $item['qr_code']['api_url'] ?? null;
                    } elseif (is_string($item['qr_code'])) {
                        $qrCodeUrl = $item['qr_code'];
                    }
                }

                $records[] = [
                    'nik' => $nik,
                    'api_id' => isset($item['id']) ? (int) $item['id'] : null,
                    'nama' => $nama,
                    'divisi' => isset($item['divisi']) ? trim((string) $item['divisi']) : null,
                    'jabatan' => isset($item['jabatan']) ? trim((string) $item['jabatan']) : null,
                    'gender' => $gender,
                    'qr_code_url' => $qrCodeUrl,
                    'is_active' => true,
                    'synced_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($records)) {
                Karyawan::upsert(
                    $records,
                    ['nik'],
                    ['api_id', 'nama', 'divisi', 'jabatan', 'gender', 'qr_code_url', 'is_active', 'synced_at', 'updated_at']
                );
                $totalSynced += count($records);
            }
        }

        Log::info("Sinkronisasi master karyawan berhasil: {$totalSynced} dari {$totalFetched} karyawan.");

        return [
            'total_fetched' => $totalFetched,
            'total_synced' => $totalSynced,
        ];
    }
}
