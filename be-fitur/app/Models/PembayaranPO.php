<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranPO extends Model
{
    protected $table = 'DBPembayaranPO';
    protected $fillable = ['NoBukti', 'Keterangan', 'DP', 'Persentase', 'KodeVls', 'Nilai'];
    protected $casts = ['DP' => 'boolean', 'Persentase' => 'float', 'Nilai' => 'float'];
}
