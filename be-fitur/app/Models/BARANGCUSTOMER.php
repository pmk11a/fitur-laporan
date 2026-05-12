<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BARANGCUSTOMER extends Model
{
    protected $table = 'DBBARANGCUSTOMER';
    protected $fillable = ['KodecustSupp', 'KodeBrg', 'Sat_1', 'Harga_1', 'Sat_2', 'Harga_2', 'Harga', 'Komisi'];
    protected $casts = ['Harga_1' => 'float', 'Harga_2' => 'float', 'Harga' => 'float', 'Komisi' => 'float'];
}
