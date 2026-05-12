<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPajak extends Model
{
    protected $table = 'DBMasterPajak';
    protected $fillable = ['Bulan', 'Tahun', 'PPn', 'Service'];
    protected $casts = ['Bulan' => 'integer', 'Tahun' => 'integer', 'PPn' => 'integer', 'Service' => 'integer'];
}
