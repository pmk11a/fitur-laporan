<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevGudang extends Model
{
    protected $table = 'DBDevGudang';
    protected $fillable = ['Devisi', 'KodeGdg'];
}
