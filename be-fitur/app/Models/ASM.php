<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ASM extends Model
{
    protected $table = 'DBASM';
    protected $fillable = ['KeyNIK', 'Area'];
    protected $casts = ['KeyNIK' => 'integer'];
}
