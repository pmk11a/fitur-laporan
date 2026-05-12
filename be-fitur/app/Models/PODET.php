<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PODET extends Model
{
    protected $table = 'DBPODET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'PPN', 'KURS', 'DISC', 'QNT', 'QntBatal', 'TglBatal', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'DISCP', 'DISCTOT', 'BYANGKUT', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'SUBTOTALRp', 'NoPPL', 'UrutPPL', 'IsClose', 'Tolerate', 'DiscP2', 'DiscP3', 'DiscP4', 'DiscP5', 'Isbatal', 'UserBatal', 'PPhP', 'NPPHRP', 'NPPH', 'Ketbrg', 'PPnP', 'NDPP', 'NPPN', 'NDPPRp', 'NPPNRp', 'NNET', 'NNETRP'];
    protected $casts = ['URUT' => 'integer', 'PPN' => 'integer', 'KURS' => 'float', 'DISC' => 'float', 'QNT' => 'float', 'QntBatal' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'DISCP' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'SUBTOTALRp' => 'float', 'UrutPPL' => 'integer', 'IsClose' => 'boolean', 'Tolerate' => 'float', 'DiscP2' => 'float', 'DiscP3' => 'float', 'DiscP4' => 'float', 'DiscP5' => 'float', 'Isbatal' => 'boolean', 'PPhP' => 'float', 'NPPHRP' => 'float', 'NPPH' => 'float', 'PPnP' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNET' => 'float', 'NNETRP' => 'float'];
}
