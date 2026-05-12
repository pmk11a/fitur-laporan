<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoHasilProduksi extends Model
{
    protected $table = 'DbSaldoHasilProduksi';
    protected $fillable = ['NamaCustSupp', 'JenisBrg', 'NamaSubGrp', 'NoSPK', 'HppSaldoAwal', 'Hpd', 'Spb', 'sisa', 'Kategori', 'Tanggal'];
    protected $casts = ['HppSaldoAwal' => 'float', 'Hpd' => 'float', 'Spb' => 'float', 'sisa' => 'float'];
}
