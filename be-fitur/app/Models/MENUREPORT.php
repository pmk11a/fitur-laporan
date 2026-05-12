<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MENUREPORT extends Model
{
    protected $table = 'DBMENUREPORT';
    protected $fillable = ['KODEMENU', 'Keterangan', 'L0', 'ACCESS', 'OL'];
    protected $casts = ['L0' => 'integer', 'ACCESS' => 'integer', 'OL' => 'integer'];
}
