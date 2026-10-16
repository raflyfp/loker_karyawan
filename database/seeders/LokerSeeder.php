<?php

namespace Database\Seeders;

use App\Models\Loker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LokerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        $records = [];

        // Loker Laki-laki: 1 s/d 100
        for ($i = 1; $i <= 100; $i++) {
            $records[] = [
                'kode_loker' => "L-{$i}",
                'nomor_urut' => $i,
                'gender_group' => 'L',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Loker Perempuan: 1 s/d 100
        for ($i = 1; $i <= 100; $i++) {
            $records[] = [
                'kode_loker' => "P-{$i}",
                'nomor_urut' => $i,
                'gender_group' => 'P',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Upsert agar dapat dijalankan ulang tanpa duplicate error
        Loker::upsert($records, ['gender_group', 'nomor_urut'], ['kode_loker', 'is_active', 'updated_at']);
    }
}
