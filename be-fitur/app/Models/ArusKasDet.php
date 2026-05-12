<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArusKasDet extends Model
{
    protected $table = 'DBArusKasDet';
    protected $fillable = ['KodeSubAK', 'KodeAK', 'NamaSubAK'];
}
