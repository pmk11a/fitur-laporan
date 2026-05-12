<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiskonHarian extends Model
{
    protected $table = 'DBDiskonHarian';
    protected $fillable = ['Hari', 'Diskon', 'Aktif'];
    protected $casts = ['Hari' => 'integer', 'Diskon' => 'float', 'Aktif' => 'boolean'];
}
