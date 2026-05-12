<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NOMORPK extends Model
{
    protected $table = 'DBNOMORPK';
    protected $fillable = ['Tipe', 'NOURUT', 'NOBUKTI', 'USERID', 'Bulan', 'Tahun', 'flagtipe', 'KodeGdg'];
    protected $casts = ['Bulan' => 'integer', 'Tahun' => 'integer', 'flagtipe' => 'integer'];
}
