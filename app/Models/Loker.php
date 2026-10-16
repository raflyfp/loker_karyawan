<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loker extends Model
{
    use HasFactory;

    protected $table = 'lokers';

    protected $fillable = [
        'kode_loker',
        'nomor_urut',
        'gender_group',
        'is_active',
    ];

    protected $casts = [
        'nomor_urut' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke seluruh riwayat alokasi pada loker ini
     */
    public function alokasiLokers(): HasMany
    {
        return $this->hasMany(AlokasiLoker::class, 'loker_id');
    }

    /**
     * Relasi ke alokasi aktif yang sedang menempati loker ini
     */
    public function alokasiAktif(): HasOne
    {
        return $this->hasOne(AlokasiLoker::class, 'loker_id')->where('status', 'active');
    }

    /**
     * Scope untuk mengambil loker yang aktif dan belum terisi
     */
    public function scopeTersedia(Builder $query, string $genderGroup): Builder
    {
        return $query->where('gender_group', $genderGroup)
            ->where('is_active', true)
            ->whereDoesntHave('alokasiAktif')
            ->orderBy('nomor_urut', 'asc');
    }
}
