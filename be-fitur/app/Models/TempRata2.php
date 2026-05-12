<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempRata2 extends Model
{
    protected $table = 'dbTempRata2';
    protected $fillable = ['KodeGdg', 'QntSaldo', 'HrgSaldo', 'HrgRata'];
    protected $casts = ['QntSaldo' => 'float', 'HrgSaldo' => 'float', 'HrgRata' => 'float'];
}
