<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GUDANG extends Model
{
    protected $table = 'DBGUDANG';
    protected $fillable = ['KODEGDG', 'NAMA', 'IsRusak', 'Alamat', 'IsCust', 'MyID', 'FlagMenu', 'IsProduksi', 'istakeinout'];
    protected $casts = ['IsRusak' => 'boolean', 'IsCust' => 'boolean', 'FlagMenu' => 'integer', 'IsProduksi' => 'boolean', 'istakeinout' => 'boolean'];
}
