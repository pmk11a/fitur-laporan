<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RInvoicePLDET extends Model
{
    protected $table = 'DBRInvoicePLDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'NamaBrg', 'PPN', 'DISC', 'KURS', 'QNT', 'QNT2', 'QNTTukar', 'QNT2Tukar', 'NetW', 'NetWTukar', 'GrossW', 'GrossWTukar', 'Mesurement', 'MesurementTukar', 'SAT_1', 'SAT_2', 'Nosat', 'ISI', 'HARGA', 'DiscP1', 'DiscRp1', 'DiscP2', 'DiscRp2', 'DiscP3', 'DiscRp3', 'DiscP4', 'DiscRp4', 'DISCTOT', 'BYANGKUT', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'NDPP', 'NPPN', 'NNET', 'SUBTOTALRp', 'NDPPRp', 'NPPNRp', 'NNETRp', 'NoInvoice', 'UrutInvoice', 'Keterangan', 'UrutTrans', 'HPP', 'MyID', 'NoSPB'];
    protected $casts = ['URUT' => 'integer', 'PPN' => 'integer', 'DISC' => 'float', 'KURS' => 'float', 'QNT' => 'float', 'QNT2' => 'float', 'QNTTukar' => 'float', 'QNT2Tukar' => 'float', 'NetW' => 'float', 'NetWTukar' => 'float', 'GrossW' => 'float', 'GrossWTukar' => 'float', 'Mesurement' => 'float', 'MesurementTukar' => 'float', 'Nosat' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'DiscP1' => 'float', 'DiscRp1' => 'float', 'DiscP2' => 'float', 'DiscRp2' => 'float', 'DiscP3' => 'float', 'DiscRp3' => 'float', 'DiscP4' => 'float', 'DiscRp4' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'SUBTOTALRp' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNETRp' => 'float', 'UrutInvoice' => 'integer', 'UrutTrans' => 'integer', 'HPP' => 'float'];
}
