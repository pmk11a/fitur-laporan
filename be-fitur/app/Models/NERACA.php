<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NERACA extends Model
{
    protected $table = 'DBNERACA';
    protected $fillable = ['Perkiraan', 'Bulan', 'Tahun', 'Devisi', 'DK', 'Valas', 'Kurs', 'AwalD', 'AwalK', 'AwalDRp', 'AwalKRp', 'MD', 'MK', 'MDRp', 'MKRp', 'JPD', 'JPK', 'JPDRp', 'JPKRp', 'RLD', 'RLK', 'RLDRp', 'RLKRp', 'Budget', 'AkhirD', 'AkhirDRp', 'AkhirK', 'AkhirKRp'];
    protected $casts = ['Bulan' => 'integer', 'Tahun' => 'integer', 'DK' => 'integer', 'Kurs' => 'float', 'AwalD' => 'float', 'AwalK' => 'float', 'AwalDRp' => 'float', 'AwalKRp' => 'float', 'MD' => 'float', 'MK' => 'float', 'MDRp' => 'float', 'MKRp' => 'float', 'JPD' => 'float', 'JPK' => 'float', 'JPDRp' => 'float', 'JPKRp' => 'float', 'RLD' => 'float', 'RLK' => 'float', 'RLDRp' => 'float', 'RLKRp' => 'float', 'Budget' => 'float', 'AkhirD' => 'float', 'AkhirDRp' => 'float', 'AkhirK' => 'float', 'AkhirKRp' => 'float'];
}
