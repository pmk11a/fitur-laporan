<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TIPETRANS extends Model
{
    protected $table = 'DBTIPETRANS';
    protected $fillable = ['KODETIPE', 'Nama', 'IsJasaBeliJual'];
    protected $casts = ['IsJasaBeliJual' => 'integer'];
}
