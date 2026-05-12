<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubBrgDepart extends Model
{
    protected $table = 'DBSubBrgDepart';
    protected $fillable = ['KodeSubGrp', 'Urut', 'KodeDept'];
    protected $casts = ['Urut' => 'integer'];
}
