<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LOGFILE extends Model
{
    protected $table = 'DBLOGFILE';
    protected $fillable = ['Tahun', 'Bulan', 'Tanggal', 'Pemakai', 'Aktivitas', 'Sumber', 'NoBukti', 'Keterangan'];
    protected $casts = ['Tahun' => 'integer', 'Bulan' => 'integer'];
    public $timestamps = false;
}
