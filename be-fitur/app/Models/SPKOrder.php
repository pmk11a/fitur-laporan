<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPKOrder extends Model
{
    protected $table = 'DBSPKOrder';
    protected $fillable = ['NOBUKTI', 'URUTSPK', 'URUT', 'NOBUKTIORDER', 'URUTORDER', 'QTY'];
    protected $casts = ['URUTSPK' => 'integer', 'URUT' => 'integer', 'URUTORDER' => 'integer', 'QTY' => 'float'];
}
