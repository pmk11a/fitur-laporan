<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FLMENUREPORT extends Model
{
    protected $table = 'DBFLMENUREPORT';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['USERID', 'L1', 'Access', 'IsDesign', 'IsExport'];
    protected $casts = ['Access' => 'boolean', 'IsDesign' => 'boolean', 'IsExport' => 'boolean'];
}
