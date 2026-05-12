<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterLaporan extends Model
{
    protected $table = 'MASTER_LAPORAN';
    protected $primaryKey = 'id_laporan';
    public $timestamps = false;

    protected $fillable = [
        'KODEMENU',
        'nama_laporan',
        'deskripsi',
        'query_sumber_data',
        'status_aktif',
        'footer_bands'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'footer_bands' => 'array'
    ];

    /**
     * Get components
     */
    public function komponen()
    {
        return $this->hasMany(KomponenLaporan::class, 'id_laporan', 'id_laporan')
            ->orderBy('urutan_tampil');
    }

    /**
     * Get parameters
     */
    public function parameters()
    {
        return $this->hasMany(ParameterLaporan::class, 'id_laporan', 'id_laporan')
            ->orderBy('id_parameter');
    }

    /**
     * Get parent menu
     */
    public function menu()
    {
        return $this->belongsTo(DBMENUREPORT::class, 'KODEMENU', 'KODEMENU');
    }
}