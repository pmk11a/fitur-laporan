<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MENUDASBOARD extends Model
{
    protected $table = 'DBMENUDASBOARD';
    protected $fillable = ['UserID', 'L0', 'L1', 'NmReport', 'KodeReport', 'Access'];
    protected $casts = ['L0' => 'integer', 'KodeReport' => 'integer', 'Access' => 'integer'];
}
