<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WipMasuk extends Model
{
    protected $table = 'DbWipMasuk';
    protected $fillable = ['Nospk', 'Tanggal', 'Kodebrg', 'Bulan', 'Tahun', 'Rpbahan', 'Rpmesin', 'Rptenaker', 'rpbiaya', 'RpRbiaya', 'wip'];
    protected $casts = ['Rpbahan' => 'float', 'Rpmesin' => 'float', 'Rptenaker' => 'float', 'rpbiaya' => 'float', 'RpRbiaya' => 'float', 'wip' => 'float'];
}
