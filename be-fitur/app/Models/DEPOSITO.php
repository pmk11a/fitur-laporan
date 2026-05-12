<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DEPOSITO extends Model
{
    protected $table = 'DBDEPOSITO';
    protected $fillable = ['NoDEPOSITO', 'Bank', 'Tanggal', 'TglJatuhTempo', 'Debet', 'Kredit', 'DebetRp', 'KreditRp', 'Keterangan', 'TglBuka', 'BuktiBuka', 'UrutBuktiBuka', 'TglCair', 'BuktiCair', 'KeteranganCair', 'UrutBuktiCair', 'Kodevls', 'Kurs', 'Jumlah', 'Tipe', 'MyID'];
    protected $casts = ['Debet' => 'float', 'Kredit' => 'float', 'DebetRp' => 'float', 'KreditRp' => 'float', 'UrutBuktiBuka' => 'integer', 'UrutBuktiCair' => 'integer', 'Kurs' => 'float', 'Jumlah' => 'float'];
}
