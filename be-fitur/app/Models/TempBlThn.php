<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempBlThn extends Model
{
    protected $table = 'DBTempBlThn';
    protected $fillable = ['Bulan', 'Tahun'];
    protected $casts = ['Bulan' => 'integer', 'Tahun' => 'integer'];
}
