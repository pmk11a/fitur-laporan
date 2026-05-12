<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    protected $table = 'DBJenis';
    protected $fillable = ['KodeJnsBrg', 'Keterangan'];
}
