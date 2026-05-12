<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FLMENU extends Model
{
    protected $table = 'DBFLMENU';
    protected $fillable = ['USERID', 'L1', 'HASACCESS', 'ISTAMBAH', 'ISKOREKSI', 'ISHAPUS', 'ISCETAK', 'ISEXPORT', 'IsOtorisasi1', 'IsOtorisasi2', 'IsOtorisasi3', 'IsOtorisasi4', 'IsOtorisasi5', 'TIPE', 'IsBatal', 'pembatalan'];
    protected $casts = ['HASACCESS' => 'boolean', 'ISTAMBAH' => 'boolean', 'ISKOREKSI' => 'boolean', 'ISHAPUS' => 'boolean', 'ISCETAK' => 'boolean', 'ISEXPORT' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'IsBatal' => 'boolean', 'pembatalan' => 'boolean'];
}
