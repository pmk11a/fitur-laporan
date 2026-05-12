<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangJadiBEli extends Model
{
    protected $table = 'BarangJadiBEli';
    protected $fillable = ['Kode Barang', 'Nama Barang', 'Kode Sub', 'Kode Sub Group', 'Sat 1', 'Sat2', 'satuan', 'harga sat', 'haga'];
    protected $casts = ['Kode Sub Group' => 'float', 'satuan' => 'float', 'harga sat' => 'float', 'haga' => 'float'];
}
