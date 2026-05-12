<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CUSTOMEREVA extends Model
{
    protected $table = 'CUSTOMEREVA';
    protected $fillable = ['KDPLG', 'NMPLG', 'ALAMAT', 'KOTA', 'KODEPOS', 'TELP', 'FAX', 'TGL', 'USERAPP', 'Negara', 'perkiraan'];
}
