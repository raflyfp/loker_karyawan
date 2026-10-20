<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';

    protected $fillable = [
        'nik',
        'api_id',
        'nama',
        'divisi',
        'jabatan',
        'gender',
        'qr_code_url',
        'is_active',
        'synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'synced_at' => 'datetime',
    ];

    /**
     * Relasi ke seluruh riwayat alokasi loker
     */
    public function alokasiLokers(): HasMany
    {
        return $this->hasMany(AlokasiLoker::class, 'karyawan_id');
    }

    /**
     * Relasi ke alokasi loker yang sedang aktif
     */
    public function alokasiAktif(): HasOne
    {
        return $this->hasOne(AlokasiLoker::class, 'karyawan_id')->where('status', 'active');
    }
}
