<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubGroupJnsTambah extends Model
{
    protected $table = 'DBSubGroupJnsTambah';
    protected $fillable = ['KodeGrp', 'KodeSubGrp', 'Urut', 'Keterangan'];
    protected $casts = ['Urut' => 'integer'];
}
