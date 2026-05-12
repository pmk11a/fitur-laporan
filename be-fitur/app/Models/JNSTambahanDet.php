<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JNSTambahanDet extends Model
{
    protected $table = 'DBJNSTambahanDet';
    protected $fillable = ['KodeJnsTambahan', 'KodeSubJnsTambahan', 'NamaSubJnsTambahan'];
}
