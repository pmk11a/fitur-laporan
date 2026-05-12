<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KELOMPOK extends Model
{
    protected $table = 'DBKELOMPOK';
    protected $fillable = ['KodeKelompok', 'Keterangan', 'Perkiraan'];
}
