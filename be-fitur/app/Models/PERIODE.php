<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PERIODE extends Model
{
    protected $table = 'DBPERIODE';
    protected $fillable = ['USERID', 'BULAN', 'TAHUN'];
}
