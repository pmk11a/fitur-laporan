<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LOKASI extends Model
{
    protected $table = 'DBLOKASI';
    protected $fillable = ['KodeGdg', 'Lokasi'];
}
