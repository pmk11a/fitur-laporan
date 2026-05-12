<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JADWALPRD extends Model
{
    protected $table = 'DBJADWALPRD';
    protected $fillable = ['NOJADWAL', 'KODEMSN', 'TANGGAL', 'JAMAWAL', 'JAMAKHIR', 'ISPRODUKSI', 'NOSPK', 'KETERANGAN', 'QNTSPK', 'QNTKERJA', 'KodePrs', 'Urut', 'urutmesin', 'TarifMesin', 'JamTenaker', 'JmlTenaker', 'TarifTenaker'];
    protected $casts = ['NOJADWAL' => 'integer', 'ISPRODUKSI' => 'boolean', 'QNTSPK' => 'float', 'QNTKERJA' => 'float', 'Urut' => 'integer', 'urutmesin' => 'integer', 'TarifMesin' => 'float', 'JamTenaker' => 'float', 'JmlTenaker' => 'float', 'TarifTenaker' => 'float'];
}
