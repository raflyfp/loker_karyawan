<?php

namespace Tests\Feature;

use App\Models\AlokasiLoker;
use App\Models\Karyawan;
use App\Models\Loker;
use App\Services\KaryawanSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LokerAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan 5 loker L dan 5 loker P untuk pengujian cepat
        for ($i = 1; $i <= 5; $i++) {
            Loker::create([
                'kode_loker' => "L-{$i}",
                'nomor_urut' => $i,
                'gender_group' => 'L',
                'is_active' => true,
            ]);

            Loker::create([
                'kode_loker' => "P-{$i}",
                'nomor_urut' => $i,
                'gender_group' => 'P',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Skenario 1: Sinkronisasi master API berhasil
     */
    public function test_sync_karyawan_berhasil(): void
    {
        Http::fake([
            '*/absen/api/karyawan' => Http::response([
                'status' => true,
                'message' => 'Berhasil',
                'data' => [
                    [
                        'id' => 101,
                        'nik' => 'TEST001',
                        'nama' => 'Budi Santoso',
                        'divisi' => 'SNAM',
                        'jabatan' => 'Staff',
                    ],
                    [
                        'id' => 102,
                        'nik' => 'TEST002',
                        'nama' => 'Siti Rahma',
                        'divisi' => 'SNAM',
                        'jabatan' => 'Operator',
                    ],
                ],
                'total' => 2,
            ], 200),
        ]);

        $service = app(KaryawanSyncService::class);
        $result = $service->syncFromApi();

        $this->assertEquals(2, $result['total_fetched']);
        $this->assertEquals(2, $result['total_synced']);
        $this->assertDatabaseHas('karyawan', ['nik' => 'TEST001', 'nama' => 'Budi Santoso']);
    }

    /**
     * Skenario 2: API gagal diakses (network / 500)
     */
    public function test_sync_karyawan_api_gagal(): void
    {
        Http::fake([
            '*/absen/api/karyawan' => Http::response('Server Error', 500),
        ]);

        $this->expectException(\RuntimeException::class);
        $service = app(KaryawanSyncService::class);
        $service->syncFromApi();
    }

    /**
     * Skenario 3: Respons API tidak valid
     */
    public function test_sync_karyawan_respons_invalid(): void
    {
        Http::fake([
            '*/absen/api/karyawan' => Http::response(['data' => 'bukan array'], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        $service = app(KaryawanSyncService::class);
        $service->syncFromApi();
    }

    /**
     * Skenario 4: NKK tidak ditemukan
     */
    public function test_scan_nkk_tidak_ditemukan(): void
    {
        $response = $this->postJson(route('loker.scan'), [
            'barcode' => 'UNKNOWN999',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Skenario 5 & 6: Karyawan tanpa gender meminta pilihan gender
     */
    public function test_karyawan_tanpa_gender_meminta_pilihan_gender(): void
    {
        $karyawan = Karyawan::create([
            'nik' => 'NKK001',
            'nama' => 'Andi Wijaya',
            'gender' => null,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('loker.scan'), [
            'barcode' => 'NKK001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'action' => 'requires_gender',
                ],
            ]);
    }

    /**
     * Skenario 7: Scan pertama dengan gender berhasil mengalokasikan loker nomor terkecil
     */
    public function test_scan_pertama_berhasil_alokasi_nomor_terkecil(): void
    {
        $karyawan = Karyawan::create([
            'nik' => 'NKK002',
            'nama' => 'Bambang',
            'gender' => 'L',
            'is_active' => true,
        ]);

        $response = $this->postJson(route('loker.scan'), [
            'barcode' => 'NKK002',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'action' => 'allocated',
                    'loker' => [
                        'kode_loker' => 'L-1',
                        'nomor_urut' => 1,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('alokasi_lokers', [
            'karyawan_id' => $karyawan->id,
            'status' => 'active',
        ]);
    }

    /**
     * Skenario 8: Loker yang sudah terisi dilewati dan memilih nomor terkecil yang tersedia
     */
    public function test_loker_terisi_dilewati(): void
    {
        $karyawan1 = Karyawan::create(['nik' => 'NKK003', 'nama' => 'User 1', 'gender' => 'L', 'is_active' => true]);
        $karyawan2 = Karyawan::create(['nik' => 'NKK004', 'nama' => 'User 2', 'gender' => 'L', 'is_active' => true]);

        // User 1 scan -> dapat L-1
        $this->postJson(route('loker.scan'), ['barcode' => 'NKK003']);

        // User 2 scan -> harus dapat L-2
        $response = $this->postJson(route('loker.scan'), ['barcode' => 'NKK004']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'action' => 'allocated',
                    'loker' => [
                        'kode_loker' => 'L-2',
                        'nomor_urut' => 2,
                    ],
                ],
            ]);
    }

    /**
     * Skenario 9: Karyawan wanita mendapatkan loker kelompok perempuan
     */
    public function test_karyawan_wanita_mendapatkan_loker_perempuan(): void
    {
        $karyawan = Karyawan::create(['nik' => 'NKK005', 'nama' => 'Dewi', 'gender' => 'P', 'is_active' => true]);

        $response = $this->postJson(route('loker.scan'), ['barcode' => 'NKK005']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'action' => 'allocated',
                    'loker' => [
                        'kode_loker' => 'P-1',
                        'gender_group' => 'P',
                    ],
                ],
            ]);
    }

    /**
     * Skenario 10: Scan kedua oleh karyawan yang sama melepaskan loker
     */
    public function test_scan_kedua_melepaskan_loker(): void
    {
        $karyawan = Karyawan::create(['nik' => 'NKK006', 'nama' => 'Eko', 'gender' => 'L', 'is_active' => true]);

        // Scan pertama: Alokasi
        $this->postJson(route('loker.scan'), ['barcode' => 'NKK006']);

        // Scan kedua: Pelepasan
        $response = $this->postJson(route('loker.scan'), ['barcode' => 'NKK006']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'action' => 'released',
                    'loker' => [
                        'kode_loker' => 'L-1',
                    ],
                ],
            ]);

        // Pastikan alokasi aktif sudah selesai
        $this->assertDatabaseHas('alokasi_lokers', [
            'karyawan_id' => $karyawan->id,
            'status' => 'completed',
        ]);
        $this->assertNull($karyawan->fresh()->alokasiAktif);
    }

    /**
     * Skenario 11: Scan ketiga setelah pelepasan dapat membuat alokasi baru
     */
    public function test_scan_ketiga_membuat_alokasi_baru(): void
    {
        $karyawan = Karyawan::create(['nik' => 'NKK007', 'nama' => 'Fajar', 'gender' => 'L', 'is_active' => true]);

        // Scan 1
        $this->postJson(route('loker.scan'), ['barcode' => 'NKK007']);
        // Scan 2 (Lepas)
        $this->postJson(route('loker.scan'), ['barcode' => 'NKK007']);

        // Scan 3 (Alokasi Baru)
        $response = $this->postJson(route('loker.scan'), ['barcode' => 'NKK007']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'action' => 'allocated',
                ],
            ]);

        $this->assertNotNull($karyawan->fresh()->alokasiAktif);
    }

    /**
     * Skenario 12: Semua loker dalam kelompok penuh
     */
    public function test_loker_penuh_menolak_transaksi(): void
    {
        // Hanya ada 5 loker L, alokasikan ke 5 orang
        for ($i = 1; $i <= 5; $i++) {
            $k = Karyawan::create(['nik' => "FULL{$i}", 'nama' => "User {$i}", 'gender' => 'L', 'is_active' => true]);
            $this->postJson(route('loker.scan'), ['barcode' => "FULL{$i}"]);
        }

        // Orang ke-6 mencoba scan
        $karyawan6 = Karyawan::create(['nik' => 'FULL6', 'nama' => 'User 6', 'gender' => 'L', 'is_active' => true]);
        $response = $this->postJson(route('loker.scan'), ['barcode' => 'FULL6']);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'barcode' => ['Loker untuk kelompok Laki-laki saat ini sedang penuh.'],
            ]);
    }

    /**
     * Skenario 13: Loker yang dinonaktifkan di master tidak dialokasikan
     */
    public function test_loker_nonaktif_dilewati(): void
    {
        // Nonaktifkan L-1
        Loker::where('kode_loker', 'L-1')->update(['is_active' => false]);

        $karyawan = Karyawan::create(['nik' => 'NKK008', 'nama' => 'Gita', 'gender' => 'L', 'is_active' => true]);
        $response = $this->postJson(route('loker.scan'), ['barcode' => 'NKK008']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'action' => 'allocated',
                    'loker' => [
                        'kode_loker' => 'L-2', // Harus lompat ke L-2
                    ],
                ],
            ]);
    }

    /**
     * Skenario 14: API status mengembalikan peta status loker
     */
    public function test_api_status_peta_loker(): void
    {
        $response = $this->getJson(route('loker.status'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'summary' => [
                    'total_male',
                    'occupied_male',
                    'available_male',
                    'total_female',
                    'occupied_female',
                    'available_female',
                ],
                'male',
                'female',
            ]);
    }
}
