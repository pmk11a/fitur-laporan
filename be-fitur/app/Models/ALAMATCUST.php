<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ALAMATCUST extends Model
{
    protected $table = 'DBALAMATCUST';
    protected $fillable = ['KODECUSTSUPP', 'Nomor', 'Nama', 'Alamat', 'Telp', 'Fax'];
    protected $casts = ['Nomor' => 'integer'];
}
