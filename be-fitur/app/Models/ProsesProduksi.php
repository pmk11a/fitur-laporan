<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesProduksi extends Model
{
    protected $table = 'DbProsesProduksi';
    protected $fillable = ['NoBukti', 'Proses', 'KodeMesin', 'TglUpdate'];
    protected $casts = ['Proses' => 'integer'];
}
