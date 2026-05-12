<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HPPProduksi extends Model
{
    protected $table = 'dbHPPProduksi';
    protected $fillable = ['Tahun', 'Bulan', 'KodeBrg', 'HPPBrg'];
    protected $casts = ['Tahun' => 'integer', 'Bulan' => 'integer', 'HPPBrg' => 'float'];
}
