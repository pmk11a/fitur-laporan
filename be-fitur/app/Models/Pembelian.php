<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'DBPembelian';
    protected $fillable = ['NoBukti', 'Urut', 'Tanggal', 'JatuhTempo', 'PPn', 'KodeCustSupp', 'KodeTipe', 'KodeSubTipe', 'Qnt', 'Harga', 'NDPP', 'NPPN', 'NNet', 'AccPersediaan', 'AccPPN', 'AccHutPiut', 'IsExcel', 'KodeVls', 'Kurs', 'NDPPD', 'NPPND', 'NNetD', 'FlagSimbol'];
    protected $casts = ['Urut' => 'integer', 'PPn' => 'integer', 'Qnt' => 'float', 'Harga' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NNet' => 'float', 'IsExcel' => 'boolean', 'Kurs' => 'float', 'NDPPD' => 'float', 'NPPND' => 'float', 'NNetD' => 'float'];
}
