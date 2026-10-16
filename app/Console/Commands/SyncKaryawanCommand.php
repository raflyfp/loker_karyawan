<?php

namespace App\Console\Commands;

use App\Services\KaryawanSyncService;
use Illuminate\Console\Command;

class SyncKaryawanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loker:sync-karyawan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi master data karyawan dari API SNA Medika ke database lokal';

    /**
     * Execute the console command.
     */
    public function handle(KaryawanSyncService $syncService): int
    {
        $this->info('Memulai sinkronisasi master karyawan dari API SNA Medika...');

        try {
            $result = $syncService->syncFromApi();
            $this->info("Berhasil! {$result['total_synced']} dari {$result['total_fetched']} karyawan telah disinkronkan.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal sinkronisasi: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
