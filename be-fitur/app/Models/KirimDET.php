<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KirimDET extends Model
{
    protected $table = 'DBKirimDET';
    protected $fillable = ['NoBukti', 'KodeBrg', 'NoSat', 'Urut', 'Tanggal', 'Qnt'];
    protected $casts = ['NoSat' => 'integer', 'Urut' => 'integer', 'Qnt' => 'float'];
}
