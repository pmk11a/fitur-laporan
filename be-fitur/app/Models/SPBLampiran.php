<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPBLampiran extends Model
{
    protected $table = 'dbSPBLampiran';
    protected $fillable = ['Urut', 'NoSPB', 'UrutSPB', 'NOPALLET', 'NOROLL', 'NOLOT', 'Sat_1', 'Sat_2', 'Qnt', 'Qnt2', 'Nosat', 'Isi', 'Keterangan', 'NetW', 'GrossW', 'HPP', 'MyID'];
    protected $casts = ['Urut' => 'integer', 'UrutSPB' => 'integer', 'Qnt' => 'float', 'Qnt2' => 'float', 'Nosat' => 'integer', 'Isi' => 'float', 'NetW' => 'float', 'GrossW' => 'float', 'HPP' => 'float'];
}
