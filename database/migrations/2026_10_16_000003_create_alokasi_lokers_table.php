<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alokasi_lokers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->foreignId('loker_id')->constrained('lokers')->cascadeOnDelete();
            $table->timestamp('waktu_masuk')->useCurrent();
            $table->timestamp('waktu_keluar')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active')->index();
            $table->string('idempotency_key', 64)->nullable()->index();

            // Generated columns untuk menjamin hanya ada 1 alokasi aktif per karyawan dan per loker
            // Pada MySQL, NULL tidak melanggar unique constraint
            $table->unsignedBigInteger('active_karyawan_id')
                ->nullable()
                ->virtualAs("CASE WHEN status = 'active' THEN karyawan_id ELSE NULL END");
            $table->unsignedBigInteger('active_loker_id')
                ->nullable()
                ->virtualAs("CASE WHEN status = 'active' THEN loker_id ELSE NULL END");

            $table->unique('active_karyawan_id', 'uq_active_karyawan');
            $table->unique('active_loker_id', 'uq_active_loker');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alokasi_lokers');
    }
};
