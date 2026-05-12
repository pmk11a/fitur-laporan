<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKBJDET extends Model
{
    protected $table = 'DBSPKBJDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'QNT', 'NOSAT', 'SATUAN', 'ISI'];
    protected $casts = ['URUT' => 'integer', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float'];
}
