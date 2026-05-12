<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoSubGroup extends Model
{
    protected $table = 'DbPromoSubGroup';
    protected $fillable = ['KodePromo', 'KodeSubGrp'];
}
