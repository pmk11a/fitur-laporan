<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TARGETSALES extends Model
{
    protected $table = 'DBTARGETSALES';
    protected $fillable = ['KeyNik', 'Tahun', 'Rp1', 'Rp2', 'Rp3', 'Rp4', 'Rp5', 'Rp6', 'Rp7', 'Rp8', 'Rp9', 'Rp10', 'Rp11', 'Rp12', 'QNT1', 'QNT2', 'QNT3', 'QNT4', 'QNT5', 'QNT6', 'QNT7', 'QNT8', 'QNT9', 'QNT10', 'QNT11', 'QNT12'];
    protected $casts = ['Tahun' => 'integer', 'Rp1' => 'float', 'Rp2' => 'float', 'Rp3' => 'float', 'Rp4' => 'float', 'Rp5' => 'float', 'Rp6' => 'float', 'Rp7' => 'float', 'Rp8' => 'float', 'Rp9' => 'float', 'Rp10' => 'float', 'Rp11' => 'float', 'Rp12' => 'float', 'QNT1' => 'float', 'QNT2' => 'float', 'QNT3' => 'float', 'QNT4' => 'float', 'QNT5' => 'float', 'QNT6' => 'float', 'QNT7' => 'float', 'QNT8' => 'float', 'QNT9' => 'float', 'QNT10' => 'float', 'QNT11' => 'float', 'QNT12' => 'float'];
}
