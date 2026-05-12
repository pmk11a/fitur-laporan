<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KOTA extends Model
{
    protected $table = 'DBKOTA';
    protected $fillable = ['KodeKota', 'NamaKota', 'KodeArea'];
}
