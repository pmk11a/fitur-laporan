<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TUJUANTRANSFER extends Model
{
    protected $table = 'DBTUJUANTRANSFER';
    protected $fillable = ['IDTUJUAN', 'NAMATUJUAN', 'CONNSTR', 'SIMBOLTUJUAN'];
    protected $casts = ['IDTUJUAN' => 'integer'];
}
