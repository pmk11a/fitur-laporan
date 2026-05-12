<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DATABARANG extends Model
{
    protected $table = 'DBDATABARANG';
    protected $fillable = ['KODEBRG', 'QNTBPPB', 'QNTPB', 'QNTPPL', 'QNTPO', 'QNTPBL', 'QNTRPB', 'QNTPNJ', 'QNTRPJ', 'QNTADI', 'QNTADO', 'QNTUKI', 'QNTUKO', 'QNTTRI', 'QNTTRO', 'QNTOSBPPB', 'QNTOSPPL'];
    protected $casts = ['QNTBPPB' => 'float', 'QNTPB' => 'float', 'QNTPPL' => 'float', 'QNTPO' => 'float', 'QNTPBL' => 'float', 'QNTRPB' => 'float', 'QNTPNJ' => 'float', 'QNTRPJ' => 'float', 'QNTADI' => 'float', 'QNTADO' => 'float', 'QNTUKI' => 'float', 'QNTUKO' => 'float', 'QNTTRI' => 'float', 'QNTTRO' => 'float', 'QNTOSBPPB' => 'float', 'QNTOSPPL' => 'float'];
}
