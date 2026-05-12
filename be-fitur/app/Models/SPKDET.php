<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKDET extends Model
{
    protected $table = 'DBSPKDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'QntBOMX', 'KodeBOMDet', 'StrLevelBOM', 'IntLevelBOM'];
    protected $casts = ['URUT' => 'integer', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'QntBOMX' => 'float', 'IntLevelBOM' => 'integer'];
}
