<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PBIAYA extends Model
{
    protected $table = 'DBPBIAYA';
    protected $fillable = ['Kodebiaya', 'Keterangan', 'Nilai', 'KodeVls', 'Kurs', 'NoBuktiInv', 'Urut'];
    protected $casts = ['Nilai' => 'float', 'Kurs' => 'float', 'Urut' => 'integer'];
}
