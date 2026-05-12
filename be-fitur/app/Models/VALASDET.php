<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VALASDET extends Model
{
    protected $table = 'DBVALASDET';
    protected $fillable = ['Kodevls', 'Tanggal', 'Kurs'];
    protected $casts = ['Kurs' => 'float'];
}
