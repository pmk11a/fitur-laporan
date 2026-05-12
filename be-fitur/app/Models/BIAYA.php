<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BIAYA extends Model
{
    protected $table = 'DBBIAYA';
    protected $fillable = ['Kodebiaya', 'Keterangan', 'MyID', 'Perkiraan'];
}
