<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPBDet extends Model
{
    protected $table = 'dbSPBDet';
    protected $fillable = ['NoBukti', 'Urut', 'NoSPP', 'UrutSPP', 'KodeBrg', 'Namabrg', 'QNT', 'QNT2', 'SAT_1', 'SAT_2', 'NOSAT', 'ISI', 'NetW', 'GrossW', 'HPP', 'KodeGdg', 'isCetakKitir'];
    protected $casts = ['Urut' => 'integer', 'UrutSPP' => 'integer', 'QNT' => 'float', 'QNT2' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'NetW' => 'float', 'GrossW' => 'float', 'HPP' => 'float', 'isCetakKitir' => 'boolean'];
}
