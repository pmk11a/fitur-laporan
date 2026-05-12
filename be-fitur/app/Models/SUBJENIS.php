<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SUBJENIS extends Model
{
    protected $table = 'DBSUBJENIS';
    protected $fillable = ['kodesubJnsBrg', 'Keterangan', 'KodeJnsBrg'];
}
