<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenLaporan extends Model
{
    protected $table = 'KOMPONEN_LAPORAN';
    protected $primaryKey = 'id_komponen';
    public $timestamps = false;

    protected $fillable = [
        'id_laporan',
        'tipe_band',
        'konfigurasi_layout',
        'urutan_tampil',
        'deskripsi'
    ];

    protected $casts = [
        'urutan_tampil' => 'integer'
    ];

    /**
     * Get report master
     */
    public function master()
    {
        return $this->belongsTo(MasterLaporan::class, 'id_laporan', 'id_laporan');
    }

    /**
     * Get decoded layout configuration
     */
    public function getLayoutConfigAttribute(): array
    {
        if (empty($this->konfigurasi_layout)) {
            return [];
        }

        return json_decode($this->konfigurasi_layout, true) ?? [];
    }
}