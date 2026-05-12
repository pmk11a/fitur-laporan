<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JUALDET extends Model
{
    protected $table = 'DBJUALDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'KODEGDG', 'TGLKIRIM', 'PPN', 'DISC', 'KURS', 'QNT', 'QNT2', 'QNTBATAL', 'TGLBATAL', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'HPP', 'DISCP1', 'DISCRp1', 'DISCTOT', 'BYANGKUT', 'NOSO', 'URUTSO', 'NOSJLN', 'URUTSJLN', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'NDPP', 'NPPN', 'NNET', 'SUBTOTALRp', 'NDPPRp', 'NPPNRp', 'NNETRp', 'NetW', 'GrossW'];
    protected $casts = ['URUT' => 'integer', 'PPN' => 'integer', 'DISC' => 'float', 'KURS' => 'float', 'QNT' => 'float', 'QNT2' => 'float', 'QNTBATAL' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'HPP' => 'float', 'DISCP1' => 'float', 'DISCRp1' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'URUTSO' => 'integer', 'URUTSJLN' => 'integer', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'SUBTOTALRp' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNETRp' => 'float', 'NetW' => 'float', 'GrossW' => 'float'];
}
