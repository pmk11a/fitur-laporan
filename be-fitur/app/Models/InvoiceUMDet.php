<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceUMDet extends Model
{
    protected $table = 'dbInvoiceUMDet';
    protected $fillable = ['NoBukti', 'Urut', 'NoSPB', 'UrutSPB', 'KodeBrg', 'Namabrg', 'ShippingMark', 'PPN', 'DISC', 'KURS', 'QNT', 'QNT2', 'SAT_1', 'SAT_2', 'NOSAT', 'ISI', 'NetW', 'GrossW', 'Meas', 'HARGA', 'DiscP', 'DiscRp', 'DISCTOT', 'HrgNetto', 'NDISKON', 'SUBTOTAL', 'NDPP', 'NPPN', 'NNET', 'NDISKONRp', 'SUBTOTALRp', 'NDPPRp', 'NPPNRp', 'NNETRp', 'KetDetail', 'MyID', 'HPP', 'NoSPP', 'TGLSPP', 'NoSO', 'TGLSO', 'PoNO', 'UrutTrans', 'DiscP2', 'DiscP3', 'DiscP4', 'DiscP5'];
    protected $casts = ['Urut' => 'integer', 'UrutSPB' => 'integer', 'PPN' => 'integer', 'DISC' => 'float', 'KURS' => 'float', 'QNT' => 'float', 'QNT2' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'NetW' => 'float', 'GrossW' => 'float', 'Meas' => 'float', 'HARGA' => 'float', 'DiscP' => 'float', 'DiscRp' => 'float', 'DISCTOT' => 'float', 'HrgNetto' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'NDISKONRp' => 'float', 'SUBTOTALRp' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNETRp' => 'float', 'HPP' => 'float', 'UrutTrans' => 'integer', 'DiscP2' => 'float', 'DiscP3' => 'float', 'DiscP4' => 'float', 'DiscP5' => 'float'];
}
