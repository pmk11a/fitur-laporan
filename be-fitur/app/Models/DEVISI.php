<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DEVISI extends Model
{
    protected $table = 'DBDEVISI';
    protected $fillable = ['Devisi', 'NamaDevisi', 'MyID'];
}
