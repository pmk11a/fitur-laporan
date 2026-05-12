<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RBELIDET extends Model
{
    protected $table = 'DBRBELIDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'PPN', 'KURS', 'DISC', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'DISCP', 'DISCTOT', 'BYANGKUT', 'NOPBL', 'URUTPBL', 'Qnt2', 'Qnt1', 'HPP', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'NDPP', 'NPPN', 'SUBTOTALRp', 'NDPPRp', 'NPPNRp', 'DiscP2', 'DiscP3', 'DiscP4', 'DiscP5', 'PPhP', 'NPPHRP', 'NPPH', 'NNET', 'NNETRP'];
    protected $casts = ['URUT' => 'integer', 'PPN' => 'integer', 'KURS' => 'float', 'DISC' => 'float', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'DISCP' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'URUTPBL' => 'integer', 'Qnt2' => 'float', 'Qnt1' => 'float', 'HPP' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'SUBTOTALRp' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'DiscP2' => 'float', 'DiscP3' => 'float', 'DiscP4' => 'float', 'DiscP5' => 'float', 'PPhP' => 'float', 'NPPHRP' => 'float', 'NPPH' => 'float', 'NNET' => 'float', 'NNETRP' => 'float'];
}
