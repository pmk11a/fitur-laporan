<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JNSTambahan extends Model
{
    protected $table = 'DBJNSTambahan';
    protected $fillable = ['KodeJnsTambahan', 'NAMA'];
}
