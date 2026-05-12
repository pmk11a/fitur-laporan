<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SUBTIPETRANS extends Model
{
    protected $table = 'DBSUBTIPETRANS';
    protected $fillable = ['KODESUBTIPE', 'Nama', 'KODETIPE', 'Persediaan', 'PPn', 'HutPiut'];
}
