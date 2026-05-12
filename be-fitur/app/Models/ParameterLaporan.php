<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParameterLaporan extends Model
{
    protected $table = 'PARAMETER_LAPORAN';
    protected $primaryKey = 'id_parameter';
    public $timestamps = false;

    protected $fillable = [
        'id_laporan',
        'nama_filter',
        'tipe_input',
        'wajib_isi',
        'nilai_default'
    ];

    protected $casts = [
        'wajib_isi' => 'boolean'
    ];

    /**
     * Get report master
     */
    public function master()
    {
        return $this->belongsTo(MasterLaporan::class, 'id_laporan', 'id_laporan');
    }
}