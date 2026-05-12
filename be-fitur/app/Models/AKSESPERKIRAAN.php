<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AKSESPERKIRAAN extends Model
{
    protected $table = 'DBAKSESPERKIRAAN';
    protected $fillable = ['UserID', 'Perkiraan'];
}
