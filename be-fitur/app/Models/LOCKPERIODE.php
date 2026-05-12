<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LOCKPERIODE extends Model
{
    protected $table = 'DBLOCKPERIODE';
    protected $fillable = ['BULAN', 'TAHUN', 'NKBULAN', 'NKTAHUN'];
    protected $casts = ['BULAN' => 'integer', 'TAHUN' => 'integer', 'NKBULAN' => 'integer', 'NKTAHUN' => 'integer'];
}
