<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PPLDET extends Model
{
    protected $table = 'DBPPLDET';
    protected $fillable = ['Nobukti', 'urut', 'kodebrg', 'Sat', 'Nosat', 'Isi', 'Qnt', 'QntPO', 'Keterangan', 'IsClose', 'NoSPK', 'UrutSPK', 'NosatSPK', 'Isbatal', 'Tglbatal', 'UserBatal', 'Qntbatal', 'TglKirim', 'NamaBarang', 'IsJasa', 'UserID'];
    protected $casts = ['urut' => 'integer', 'Nosat' => 'integer', 'Isi' => 'float', 'Qnt' => 'float', 'QntPO' => 'float', 'IsClose' => 'boolean', 'UrutSPK' => 'integer', 'NosatSPK' => 'integer', 'Isbatal' => 'boolean', 'Qntbatal' => 'float', 'IsJasa' => 'boolean'];
}
