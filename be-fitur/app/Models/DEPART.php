<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DEPART extends Model
{
    protected $table = 'DBDEPART';
    protected $fillable = ['KDDEP', 'NMDEP', 'PerkBiaya'];
}
