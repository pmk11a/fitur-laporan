<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BJ extends Model
{
    protected $table = 'BJ';
    protected $fillable = ['Kode Barang', 'Nama', 'KodeGroup', 'KodesubGroup', 'Satuan 1', 'Satuan 2'];
}
