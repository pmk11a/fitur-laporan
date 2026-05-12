<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifTenaker extends Model
{
    protected $table = 'DBTarifTenaker';
    protected $fillable = ['KodeTarifTenaker', 'Ket', 'Tarif', 'Nik'];
    protected $casts = ['Tarif' => 'float'];
}
