<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoWipPenyesuaian extends Model
{
    protected $table = 'DbSaldoWipPenyesuaian';
    protected $fillable = ['Nospk', 'Tanggal', 'Bulan', 'Tahun', 'Rpbahan', 'Rpmesin', 'Rptenaker', 'rpbiaya', 'wip'];
    protected $casts = ['Rpbahan' => 'float', 'Rpmesin' => 'float', 'Rptenaker' => 'float', 'rpbiaya' => 'float', 'wip' => 'float'];
}
