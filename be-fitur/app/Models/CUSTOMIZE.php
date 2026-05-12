<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CUSTOMIZE extends Model
{
    protected $table = 'DBCUSTOMIZE';
    protected $fillable = ['ID', 'IDuser', 'Tipe'];
}
