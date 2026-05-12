<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BOMDET extends Model
{
    protected $table = 'DBBOMDET';
    protected $fillable = ['KodeBOM', 'Urut', 'KodeBrg', 'Qnt', 'Numerator', 'Denominator', 'LossRatio', 'PlaceCD'];
    protected $casts = ['Urut' => 'integer', 'Qnt' => 'float', 'Numerator' => 'float', 'Denominator' => 'float', 'LossRatio' => 'float'];
}
