<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoDet extends Model
{
    protected $table = 'DBPromoDet';
    protected $fillable = ['KodePromo', 'Urut', 'KodeBrg', 'KodeSupp', 'KodeGrp', 'KodeBrd', 'Diskon', 'Tipe'];
    protected $casts = ['Diskon' => 'float'];
}
