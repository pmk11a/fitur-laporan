<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerBaru extends Model
{
    protected $table = 'CustomerBaru';
    protected $fillable = ['Kodecustsupp', 'Thn', 'Noso'];
    protected $casts = ['Thn' => 'float'];
}
