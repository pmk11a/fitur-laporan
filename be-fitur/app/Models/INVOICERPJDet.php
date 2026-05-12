<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class INVOICERPJDet extends Model
{
    protected $table = 'DBINVOICERPJDet';
    protected $fillable = ['NoBukti', 'Urut', 'Kodebrg', 'NOSPR', 'UrutSPR', 'Disc', 'PPn', 'Kurs', 'SAT_1', 'SAT_2', 'Qnt', 'Qnt2', 'Nosat', 'Isi', 'Harga', 'DiscP', 'DiscRp', 'DISCTOT', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'NDPP', 'NPPN', 'NNET', 'SUBTOTALRp', 'NDPPRp', 'NPPNRp', 'NNETRp', 'Keterangan', 'UrutTrans', 'MyID', 'HPP', 'DiscP2', 'DiscP3', 'DiscP4', 'DiscP5'];
    protected $casts = ['Urut' => 'integer', 'UrutSPR' => 'integer', 'Disc' => 'float', 'PPn' => 'integer', 'Kurs' => 'float', 'Qnt' => 'float', 'Qnt2' => 'float', 'Nosat' => 'integer', 'Isi' => 'float', 'Harga' => 'float', 'DiscP' => 'float', 'DiscRp' => 'float', 'DISCTOT' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NNET' => 'float', 'SUBTOTALRp' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNETRp' => 'float', 'UrutTrans' => 'integer', 'HPP' => 'float', 'DiscP2' => 'float', 'DiscP3' => 'float', 'DiscP4' => 'float', 'DiscP5' => 'float'];
}
