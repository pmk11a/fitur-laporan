<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GROUP extends Model
{
    protected $table = 'DBGROUP';
    protected $fillable = ['KODEGRP', 'NAMA'];
}
