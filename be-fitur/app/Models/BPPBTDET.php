<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPPBTDET extends Model
{
    protected $table = 'DBBPPBTDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'NoBPPB', 'UrutBPPB', 'NoSatBPPB', 'KODEBRG', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'Qnt2', 'Qnt2M', 'Qnt2P', 'HPP', 'Pr1', 'Pr2', 'Pr3', 'Pr4', 'Pr5', 'Pr6', 'Pr7', 'Pr8', 'Pr9', 'Jml1', 'Jml2', 'Jml3', 'Jml4', 'Jml5', 'Jml6', 'Jml7', 'Jml8', 'Jml9', 'KetR1', 'KetR2', 'KetR3', 'KetR4', 'KetR5', 'KetR6', 'KetR7', 'KetR8', 'KetR9', 'QntCetak', 'QntBaik', 'QntRusak', 'Ket1', 'Ket2'];
    protected $casts = ['URUT' => 'integer', 'UrutBPPB' => 'integer', 'NoSatBPPB' => 'integer', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'Qnt2' => 'float', 'Qnt2M' => 'float', 'Qnt2P' => 'float', 'HPP' => 'float', 'Jml1' => 'float', 'Jml2' => 'float', 'Jml3' => 'float', 'Jml4' => 'float', 'Jml5' => 'float', 'Jml6' => 'float', 'Jml7' => 'float', 'Jml8' => 'float', 'Jml9' => 'float', 'QntCetak' => 'float', 'QntBaik' => 'float', 'QntRusak' => 'float'];
}
