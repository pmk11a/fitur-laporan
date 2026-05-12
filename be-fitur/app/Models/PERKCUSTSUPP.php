<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PERKCUSTSUPP extends Model
{
    protected $table = 'DBPERKCUSTSUPP';
    protected $fillable = ['KodeCustSupp', 'Urut', 'Perkiraan'];
    protected $casts = ['Urut' => 'integer'];
}
