<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proses extends Model
{
    protected $table = 'DBProses';
    protected $fillable = ['KodePrs', 'Keterangan', 'KodeGdg'];
}
