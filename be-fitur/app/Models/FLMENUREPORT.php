<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FLMENUREPORT extends Model
{
    protected $table = 'DBFLMENUREPORT';
    protected $fillable = ['UserID', 'L1', 'Access', 'IsDesign', 'Isexport'];
    protected $casts = ['Access' => 'boolean', 'IsDesign' => 'boolean', 'Isexport' => 'boolean'];
}
