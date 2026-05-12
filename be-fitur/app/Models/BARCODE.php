<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BARCODE extends Model
{
    protected $table = 'DBBARCODE';
    protected $fillable = ['NomorUrut', 'KodeBarang', 'PLU'];
    protected $casts = ['NomorUrut' => 'integer'];
}
