<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoBarangJadi extends Model
{
    protected $table = 'DbSaldoBarangJadi';
    protected $fillable = ['Nospk', 'Tanggal', 'Bulan', 'Tahun', 'Qnt', 'Rpbahan', 'Rpmesin', 'Rptenaker', 'rpbiaya', 'wip'];
    protected $casts = ['Qnt' => 'integer', 'Rpbahan' => 'float', 'Rpmesin' => 'float', 'Rptenaker' => 'float', 'rpbiaya' => 'float', 'wip' => 'float'];
}
