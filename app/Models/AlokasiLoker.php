<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiLoker extends Model
{
    use HasFactory;

    protected $table = 'alokasi_lokers';

    protected $fillable = [
        'karyawan_id',
        'loker_id',
        'waktu_masuk',
        'waktu_keluar',
        'status',
        'idempotency_key',
    ];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function loker(): BelongsTo
    {
        return $this->belongsTo(Loker::class, 'loker_id');
    }
}
