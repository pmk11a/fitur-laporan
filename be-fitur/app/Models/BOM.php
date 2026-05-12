<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BOM extends Model
{
    protected $table = 'DBBOM';
    protected $fillable = ['KodeBOM', 'KodeBrg', 'NoUrut', 'IsDefault', 'TglAwal', 'TglAkhir'];
    protected $casts = ['IsDefault' => 'boolean'];
}
