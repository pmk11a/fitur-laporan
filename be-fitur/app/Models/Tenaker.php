<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenaker extends Model
{
    protected $table = 'DBTenaker';
    protected $fillable = ['KodeTarifTenaker', 'Urut', 'NIK'];
    protected $casts = ['Urut' => 'integer'];
}
