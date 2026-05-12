<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoBarang extends Model
{
    protected $table = 'DbPromoBarang';
    protected $fillable = ['KodePromo', 'KodeBrg'];
}
