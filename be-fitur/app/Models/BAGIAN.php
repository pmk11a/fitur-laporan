<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BAGIAN extends Model
{
    protected $table = 'DBBAGIAN';
    protected $fillable = ['KodeBag', 'NamaBag', 'Perkiraan', 'Biaya', 'BiayaJasaKom', 'BiayaJasaAlat', 'MyID'];
}
