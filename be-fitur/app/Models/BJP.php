<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BJP extends Model
{
    protected $table = 'BJP';
    protected $fillable = ['kodeBrg', 'NamaBrg', 'Kodegrup', 'Kodesubgroup', 'isi1', 'Sat1', 'proses', 'Isi2', 'iskaktif'];
    protected $casts = ['isi1' => 'float', 'proses' => 'float', 'Isi2' => 'float', 'iskaktif' => 'float'];
}
