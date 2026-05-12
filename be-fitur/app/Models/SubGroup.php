<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubGroup extends Model
{
    protected $table = 'dbSubGroup';
    protected $fillable = ['KodeGrp', 'KodeSubGrp', 'NamaSubGrp', 'PerkPers', 'PerkH', 'PerkPPN', 'PerkBiaya'];
}
