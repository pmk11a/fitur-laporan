<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoBrand extends Model
{
    protected $table = 'DBPromoBrand';
    protected $fillable = ['KodePromo', 'KodeBrd'];
}
