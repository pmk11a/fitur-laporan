<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PERUSAHAAN extends Model
{
    protected $table = 'DBPERUSAHAAN';
    protected $fillable = ['KODEUSAHA', 'NAMA', 'ALAMAT1', 'ALAMAT2', 'KOTA', 'Telpon', 'Fax', 'NAMAPKP', 'ALAMATPKP1', 'ALAMATPKP2', 'KOTAPKP', 'NPWP', 'TGLPENGUKUHAN', 'NAMAPKP1', 'ALAMATPKP21', 'ALAMATPKP22', 'KOTAPKP1', 'NPWP1', 'TGLPENGUKUHAN1', 'Direksi', 'Jabatan', 'LOGO', 'TTD', 'email', 'TTD_PATH', 'LOGO_PATH'];
}
