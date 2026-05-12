<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomisiCustomer extends Model
{
    protected $table = 'DBKomisiCustomer';
    protected $fillable = ['KodeCustSupp', 'KodeBrg', 'Urut', 'Nama', 'Kurir', 'Kurir_2', 'islunas'];
    protected $casts = ['Urut' => 'integer', 'Kurir' => 'float', 'Kurir_2' => 'float', 'islunas' => 'boolean'];
}
