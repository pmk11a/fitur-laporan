<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArusKasKonfig extends Model
{
    protected $table = 'DBArusKasKonfig';
    protected $fillable = ['KodeAK', 'KodeSAK', 'Tipe', 'Keterangan', 'Nomor', 'Urutan'];
    protected $casts = ['Tipe' => 'integer'];
}
