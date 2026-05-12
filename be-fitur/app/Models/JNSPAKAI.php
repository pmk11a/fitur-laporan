<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JNSPAKAI extends Model
{
    protected $table = 'DBJNSPAKAI';
    protected $fillable = ['KodeJNSPakai', 'Keterangan', 'Perkiraan'];
}
