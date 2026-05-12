<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class apjb extends Model
{
    protected $table = 'apjb';
    protected $fillable = ['KodeBrg', 'Nama', 'KodeGrp', 'KodeSubGrp', 'KodeSupp', 'Sat1', 'isi1', 'isaktif', 'KodeBrgL'];
    protected $casts = ['isi1' => 'float', 'isaktif' => 'float'];
}
