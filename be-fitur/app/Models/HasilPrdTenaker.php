<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilPrdTenaker extends Model
{
    protected $table = 'DBHasilPrdTenaker';
    protected $fillable = ['Nobukti', 'Urut', 'Nik', 'UrutNiK', 'Jam', 'TrfTenaker', 'JmLTrfTenaker'];
    protected $casts = ['Urut' => 'integer', 'UrutNiK' => 'integer', 'Jam' => 'float', 'TrfTenaker' => 'float', 'JmLTrfTenaker' => 'float'];
}
