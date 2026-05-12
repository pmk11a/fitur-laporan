<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RPenyerahanBhnDET extends Model
{
    protected $table = 'DBRPenyerahanBhnDET';
    protected $fillable = ['Nobukti', 'urut', 'kodebrg', 'Sat', 'Nosat', 'Isi', 'Qnt', 'Qnt2', 'HPP', 'NoPenyerahanBHN', 'UrutPenyerahanBHN', 'Keterangan'];
    protected $casts = ['urut' => 'integer', 'Nosat' => 'integer', 'Isi' => 'float', 'Qnt' => 'float', 'Qnt2' => 'float', 'HPP' => 'float', 'UrutPenyerahanBHN' => 'integer'];
}
