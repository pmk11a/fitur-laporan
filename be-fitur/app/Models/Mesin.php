<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    protected $table = 'DBMesin';
    protected $fillable = ['KodeMsn', 'Ket', 'KodePrs', 'Kapasitas', 'Tarif'];
    protected $casts = ['Kapasitas' => 'float', 'Tarif' => 'float'];
}
