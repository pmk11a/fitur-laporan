<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoGroup extends Model
{
    protected $table = 'DbPromoGroup';
    protected $fillable = ['KodePromo', 'KodeGrp'];
}
