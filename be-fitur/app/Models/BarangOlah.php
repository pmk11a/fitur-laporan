<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangOlah extends Model
{
    protected $table = 'DBBarangOlah';
    protected $fillable = ['KodeBrgOlah', 'Urut', 'KODEBRG'];
    protected $casts = ['Urut' => 'integer'];
}
