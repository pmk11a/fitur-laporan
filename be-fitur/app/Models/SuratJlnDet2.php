<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJlnDet2 extends Model
{
    protected $table = 'dbSuratJlnDet2';
    protected $fillable = ['NoBukti', 'Urut', 'Qnt', 'Satuan', 'Isi'];
    protected $casts = ['Urut' => 'integer', 'Qnt' => 'float', 'Isi' => 'float'];
}
