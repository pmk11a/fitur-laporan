<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoSupplier extends Model
{
    protected $table = 'DbPromoSupplier';
    protected $fillable = ['KodePromo', 'KodeSupp'];
}
