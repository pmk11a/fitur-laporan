<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $table = 'DBPromo';
    protected $fillable = ['KodePromo', 'NamaPromo', 'TglMulai', 'TglAkhir', 'Diskon', 'TipeDiskon', 'NoUrut', 'Tanggal', 'TglInput', 'TglSinkronisasi'];
    protected $casts = ['Diskon' => 'float'];
}
