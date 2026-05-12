<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VALAS extends Model
{
    protected $table = 'dbVALAS';
    protected $fillable = ['KODEVLS', 'NAMAVLS', 'KURS', 'Simbol'];
    protected $casts = ['KURS' => 'float'];
}
