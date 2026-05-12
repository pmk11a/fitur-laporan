<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GIRO extends Model
{
    protected $table = 'DBGIRO';
    protected $fillable = ['NoGiro', 'Bank', 'TglGiro', 'Debet', 'Kredit', 'DebetRp', 'KreditRp', 'Keterangan', 'TglBuka', 'BuktiBuka', 'UrutBuktiBuka', 'TglCair', 'BuktiCair', 'KeteranganCair', 'UrutBuktiCair', 'Kodevls', 'Kurs', 'Jumlah', 'Tipe', 'MyID', 'FlagSimbol', 'Kas'];
    protected $casts = ['Debet' => 'float', 'Kredit' => 'float', 'DebetRp' => 'float', 'KreditRp' => 'float', 'UrutBuktiBuka' => 'integer', 'UrutBuktiCair' => 'integer', 'Kurs' => 'float', 'Jumlah' => 'float'];
}
