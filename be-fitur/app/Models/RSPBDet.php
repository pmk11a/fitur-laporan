<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RSPBDet extends Model
{
    protected $table = 'DBRSPBDet';
    protected $fillable = ['NoBukti', 'Urut', 'NoSPB', 'UrutSPB', 'KodeBrg', 'Namabrg', 'QNT', 'QNT2', 'SAT_1', 'SAT_2', 'NOSAT', 'ISI', 'NetW', 'GrossW', 'HPP'];
    protected $casts = ['Urut' => 'integer', 'UrutSPB' => 'integer', 'QNT' => 'float', 'QNT2' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'NetW' => 'float', 'GrossW' => 'float', 'HPP' => 'float'];
}
