<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class POSTHUTPIUT extends Model
{
    protected $table = 'DBPOSTHUTPIUT';
    protected $fillable = ['Perkiraan', 'Kode', 'Persen', 'Tipe', 'Akumulasi', 'Biaya1', 'Biaya2', 'PersenBiaya1', 'PersenBiaya2', 'Urut', 'IsBeliJual', 'IsLokalorExim'];
    protected $casts = ['Persen' => 'float', 'PersenBiaya1' => 'float', 'PersenBiaya2' => 'float', 'Urut' => 'integer', 'IsBeliJual' => 'boolean', 'IsLokalorExim' => 'boolean'];
}
