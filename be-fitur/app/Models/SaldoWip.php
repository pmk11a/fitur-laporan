<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoWip extends Model
{
    protected $table = 'DbSaldoWip';
    protected $fillable = ['Nospk', 'Tanggal', 'Bulan', 'Tahun', 'Rpbahan', 'Rpmesin', 'Rptenaker', 'rpbiaya', 'wip'];
    protected $casts = ['Rpbahan' => 'float', 'Rpmesin' => 'float', 'Rptenaker' => 'float', 'rpbiaya' => 'float', 'wip' => 'float'];
}
