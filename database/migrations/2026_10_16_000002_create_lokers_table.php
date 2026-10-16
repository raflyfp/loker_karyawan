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
        Schema::create('lokers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_loker', 20)->unique()->comment('Contoh: L-1, P-1');
            $table->unsignedInteger('nomor_urut')->index()->comment('Urutan angka 1..100 untuk sorting numerik');
            $table->enum('gender_group', ['L', 'P'])->index()->comment('L = Laki-laki, P = Perempuan');
            $table->boolean('is_active')->default(true)->index()->comment('Status operasional loker (bisa dinonaktifkan/dikunci)');
            $table->timestamps();

            // Kombinasi gender dan nomor urut unik
            $table->unique(['gender_group', 'nomor_urut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokers');
    }
};
