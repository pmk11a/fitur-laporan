<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBQueryLaporan extends Model
{
    protected $table = 'dbquerylaporan';
    protected $primaryKey = 'id_query';
    public $timestamps = false;

    protected $fillable = [
        'id_laporan',
        'nama_dataset',
        'query_sumber_data',
        'deskripsi',
        'urutan'
    ];

    protected $casts = [
        'urutan' => 'integer'
    ];

    public function master()
    {
        return $this->belongsTo(MasterLaporan::class, 'id_laporan', 'id_laporan');
    }
}