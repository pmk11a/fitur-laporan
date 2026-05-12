<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JualTunaiDet extends Model
{
    protected $table = 'dbJualTunaiDet';
    protected $fillable = ['NOBUKTI', 'URUT', 'KodeBrg', 'HARGA', 'DISCP', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'Ctk', 'Diskon', 'hrgnetto', 'subtotal', 'TglBatal', 'isGratis', 'Keterangan', 'IsSelesai', 'IsKirim', 'TakeIn', 'TakeOut', 'UserIdBatal', 'KetBatal', 'Isbatal', 'ISUserBatal'];
    protected $casts = ['URUT' => 'integer', 'HARGA' => 'float', 'DISCP' => 'float', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'Ctk' => 'boolean', 'Diskon' => 'float', 'hrgnetto' => 'float', 'subtotal' => 'float', 'isGratis' => 'boolean', 'IsSelesai' => 'boolean', 'IsKirim' => 'boolean', 'TakeIn' => 'boolean', 'TakeOut' => 'boolean', 'Isbatal' => 'integer'];
}
