<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HARGAJUAL extends Model
{
    protected $table = 'DBHARGAJUAL';
    protected $fillable = ['KODEBRG', 'KODEJENISCUSTSUPP', 'HARGA1', 'HARGA2'];
    protected $casts = ['HARGA1' => 'float', 'HARGA2' => 'float'];
}
