<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EXPEDISI extends Model
{
    protected $table = 'DBEXPEDISI';
    protected $fillable = ['KODEEXP', 'NAMAEXP', 'ALAMAT1', 'ALAMAT2', 'KOTA', 'KODEPOS', 'TELPON', 'HP', 'FAX', 'EMAIL', 'Contact', 'Perkiraan', 'Aktif'];
    protected $casts = ['Aktif' => 'integer'];
}
