<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SODET extends Model
{
    protected $table = 'DBSODET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'TGLKIRIM', 'PPN', 'DISC', 'KURS', 'QNT', 'QNT2', 'QNTBATAL', 'TGLBATAL', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'HPP', 'DISCP1', 'DISCRP1', 'DISCTOT', 'BYANGKUT', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'SUBTOTALRp', 'NOSPB', 'UrutSPB', 'Qnt3', 'QntSisaSO', 'Qnt2SisaSO', 'QntSJln', 'Qnt2SJln', 'IsCetakKitir', 'DiscP2', 'DiscP3', 'DiscP4', 'DiscP5', 'IsCloseDet', 'IsHSO', 'IsLockMkt', 'IsUpdate', 'isReprosesRevisi', 'PPnP', 'NPPN', 'NPPNRp', 'NNET', 'NNETRp', 'NDPP', 'NDPPRp'];
    protected $casts = ['URUT' => 'integer', 'PPN' => 'integer', 'DISC' => 'float', 'KURS' => 'float', 'QNT' => 'float', 'QNT2' => 'float', 'QNTBATAL' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'HPP' => 'float', 'DISCP1' => 'float', 'DISCRP1' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'SUBTOTALRp' => 'float', 'UrutSPB' => 'integer', 'Qnt3' => 'float', 'QntSisaSO' => 'float', 'Qnt2SisaSO' => 'float', 'QntSJln' => 'float', 'Qnt2SJln' => 'float', 'IsCetakKitir' => 'boolean', 'DiscP2' => 'float', 'DiscP3' => 'float', 'DiscP4' => 'float', 'DiscP5' => 'float', 'IsCloseDet' => 'boolean', 'IsHSO' => 'boolean', 'IsLockMkt' => 'boolean', 'IsUpdate' => 'boolean', 'isReprosesRevisi' => 'boolean', 'PPnP' => 'float', 'NPPN' => 'float', 'NPPNRp' => 'float', 'NNET' => 'float', 'NNETRp' => 'float', 'NDPP' => 'float', 'NDPPRp' => 'float'];
}
