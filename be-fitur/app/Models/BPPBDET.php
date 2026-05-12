<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPPBDET extends Model
{
    protected $table = 'DBBPPBDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'Qnt2', 'Qnt2M', 'Qnt2P', 'Konversi', 'Keterangan'];
    protected $casts = ['URUT' => 'integer', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'Qnt2' => 'float', 'Qnt2M' => 'float', 'Qnt2P' => 'float', 'Konversi' => 'float'];
}
