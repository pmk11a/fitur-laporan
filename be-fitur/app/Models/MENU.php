<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MENU extends Model
{
    protected $table = 'DBMENU';
    protected $fillable = ['KODEMENU', 'Keterangan', 'L0', 'ACCESS', 'OL', 'TipeTrans', 'routename', 'icon'];
    protected $casts = ['L0' => 'integer', 'ACCESS' => 'integer', 'OL' => 'integer'];
}
