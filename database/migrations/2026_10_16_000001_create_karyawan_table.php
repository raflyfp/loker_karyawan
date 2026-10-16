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
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 50)->unique()->comment('NKK / NIK identitas kartu barcode');
            $table->unsignedInteger('api_id')->nullable()->index()->comment('ID asli dari API absen');
            $table->string('nama', 150);
            $table->string('divisi', 100)->nullable();
            $table->string('jabatan', 100)->nullable();
            $table->enum('gender', ['L', 'P'])->nullable()->index()->comment('L = Laki-laki, P = Perempuan');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
