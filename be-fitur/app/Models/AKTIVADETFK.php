<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AKTIVADETFK extends Model
{
    protected $table = 'DBAKTIVADETFK';
    protected $fillable = ['Perkiraan', 'Bulan', 'Tahun', 'Devisi', 'Valas', 'Kurs', 'Awal', 'AwalSusut', 'AwalD', 'AwalSusutD', 'MD', 'DMD', 'MK', 'DMK', 'SD', 'DSD', 'SK', 'DSK', 'Akhir', 'AkhirD', 'AkhirSusutD', 'MyID', 'AkhirSusut'];
    protected $casts = ['Bulan' => 'integer', 'Tahun' => 'integer', 'Kurs' => 'float', 'Awal' => 'float', 'AwalSusut' => 'float', 'AwalD' => 'float', 'AwalSusutD' => 'float', 'MD' => 'float', 'DMD' => 'float', 'MK' => 'float', 'DMK' => 'float', 'SD' => 'float', 'DSD' => 'float', 'SK' => 'float', 'DSK' => 'float', 'Akhir' => 'float', 'AkhirD' => 'float', 'AkhirSusutD' => 'float', 'AkhirSusut' => 'float'];
}
