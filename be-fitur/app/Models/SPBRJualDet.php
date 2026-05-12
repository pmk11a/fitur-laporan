<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPBRJualDet extends Model
{
    protected $table = 'dbSPBRJualDet';
    protected $fillable = ['NoBukti', 'Urut', 'Noinv', 'UrutInv', 'KodeBrg', 'Namabrg', 'QNT', 'QNT2', 'SAT_1', 'SAT_2', 'NOSAT', 'ISI', 'NetW', 'GrossW', 'HPP', 'KodeGdg'];
    protected $casts = ['Urut' => 'integer', 'UrutInv' => 'integer', 'QNT' => 'float', 'QNT2' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'NetW' => 'float', 'GrossW' => 'float', 'HPP' => 'float'];
}
