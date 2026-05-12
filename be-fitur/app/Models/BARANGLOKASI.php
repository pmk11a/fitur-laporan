<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BARANGLOKASI extends Model
{
    protected $table = 'DBBARANGLOKASI';
    protected $fillable = ['KodeGdg', 'Lokasi', 'KodeBrg'];
}
