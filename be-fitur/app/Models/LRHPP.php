<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LRHPP extends Model
{
    protected $table = 'DBLRHPP';
    protected $fillable = ['Bulan', 'Tahun', 'Devisi', 'Perkiraan', 'Nomor', 'Keterangan', 'Grup', 'Tipe', 'Tanda', 'Persen', 'Jumlah', 'Tampil', 'TotalA', 'TotalB', 'TotalC', 'IsLRHPP'];
    protected $casts = ['Bulan' => 'integer', 'Tahun' => 'integer', 'TotalA' => 'float', 'TotalB' => 'float', 'TotalC' => 'float', 'IsLRHPP' => 'boolean'];
}
