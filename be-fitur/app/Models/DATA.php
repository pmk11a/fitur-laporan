<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DATA extends Model
{
    protected $table = 'DBDATA';
    protected $fillable = ['KODETAB', 'KODEDATA', 'Nama'];
}
