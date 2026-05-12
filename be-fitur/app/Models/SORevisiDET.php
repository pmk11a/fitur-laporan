<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SORevisiDET extends Model
{
    protected $table = 'DBSORevisiDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'RevisiKe', 'KODEBRG', 'TGLKIRIM', 'PPN', 'DISC', 'KURS', 'QNT', 'QNT2', 'QNTBATAL', 'TGLBATAL', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'HPP', 'DISCP1', 'DISCRP1', 'DISCTOT', 'BYANGKUT', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'NDPP', 'NPPN', 'NNET', 'SUBTOTALRp', 'NDPPRp', 'NPPNRp', 'NNETRp', 'NOSPB', 'UrutSPB', 'Qnt3', 'QntSisaSO', 'Qnt2SisaSO', 'QntSJln', 'Qnt2SJln', 'IsCetakKitir', 'NoContainer', 'Ukuran', 'NoSlabs'];
    protected $casts = ['URUT' => 'integer', 'RevisiKe' => 'integer', 'PPN' => 'integer', 'DISC' => 'float', 'KURS' => 'float', 'QNT' => 'float', 'QNT2' => 'float', 'QNTBATAL' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'HPP' => 'float', 'DISCP1' => 'float', 'DISCRP1' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'SUBTOTALRp' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNETRp' => 'float', 'UrutSPB' => 'integer', 'Qnt3' => 'float', 'QntSisaSO' => 'float', 'Qnt2SisaSO' => 'float', 'QntSJln' => 'float', 'Qnt2SJln' => 'float', 'IsCetakKitir' => 'boolean', 'NoSlabs' => 'integer'];
}
