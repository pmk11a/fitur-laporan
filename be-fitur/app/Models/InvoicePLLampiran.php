<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePLLampiran extends Model
{
    protected $table = 'DBInvoicePLLampiran';
    protected $fillable = ['Nobukti', 'Urut', 'Keterangan', 'KodeVls', 'Kurs', 'Qnt', 'Qnt2', 'Nosat', 'Sat_1', 'Sat_2', 'Harga', 'NNet', 'NNetRp'];
    protected $casts = ['Urut' => 'integer', 'Kurs' => 'float', 'Qnt' => 'float', 'Qnt2' => 'float', 'Nosat' => 'integer', 'Harga' => 'float', 'NNet' => 'float', 'NNetRp' => 'float'];
}
