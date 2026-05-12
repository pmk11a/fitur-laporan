<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenyerahanBhnDET extends Model
{
    protected $table = 'DBPenyerahanBhnDET';
    protected $fillable = ['Nobukti', 'urut', 'NoSPK', 'UrutSPK', 'NoSatSPK', 'kodebrg', 'Sat', 'Nosat', 'Isi', 'Qnt', 'Qnt2', 'HPP', 'Keterangan', 'TglInput'];
    protected $casts = ['urut' => 'integer', 'UrutSPK' => 'integer', 'NoSatSPK' => 'integer', 'Nosat' => 'integer', 'Isi' => 'float', 'Qnt' => 'float', 'Qnt2' => 'float', 'HPP' => 'float'];
}
