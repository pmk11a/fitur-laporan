<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JABATAN extends Model
{
    protected $table = 'DBJABATAN';
    protected $fillable = ['KODEJAB', 'NamaJab', 'MyID'];
}
