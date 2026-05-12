<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyUrutan extends Model
{
    protected $table = 'dbMyUrutan';
    protected $fillable = ['KodeData', 'Urutan', 'KodeUrutan'];
    protected $casts = ['Urutan' => 'integer'];
}
