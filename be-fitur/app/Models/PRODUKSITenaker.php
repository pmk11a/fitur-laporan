<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PRODUKSITenaker extends Model
{
    protected $table = 'DBPRODUKSITenaker';
    protected $fillable = ['Nobukti', 'Urut', 'Nik', 'UrutNiK', 'Jam', 'TrfTenaker', 'QntAktualTK', 'KeteranganTK', 'JamL', 'JmLTrfTenaker', 'JamTenaker'];
    protected $casts = ['Urut' => 'integer', 'UrutNiK' => 'integer', 'Jam' => 'float', 'TrfTenaker' => 'float', 'QntAktualTK' => 'float', 'JamL' => 'float', 'JmLTrfTenaker' => 'float', 'JamTenaker' => 'float'];
}
