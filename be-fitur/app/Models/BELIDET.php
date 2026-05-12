<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BELIDET extends Model
{
    protected $table = 'DBBELIDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'KodeGdg', 'PPN', 'KURS', 'DISC', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'HARGA', 'DISCP', 'DISCTOT', 'BYANGKUT', 'NoPO', 'UrutPO', 'HPP', 'QntTerima', 'Qnt1Terima', 'Qnt2Terima', 'QntReject', 'Qnt1Reject', 'Qnt2Reject', 'HRGNETTO', 'NDISKON', 'SUBTOTAL', 'SUBTOTALRp', 'UrutBeli', 'KetReject', 'DiscP2', 'DiscP3', 'DiscP4', 'DiscP5', 'PPhP', 'NPPHRP', 'NPPH', 'NamaBarang', 'nospk', 'PPnP', 'NDPP', 'NPPN', 'NDPPRp', 'NPPNRp', 'NNET', 'NNETRP'];
    protected $casts = ['URUT' => 'integer', 'PPN' => 'integer', 'KURS' => 'float', 'DISC' => 'float', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HARGA' => 'float', 'DISCP' => 'float', 'DISCTOT' => 'float', 'BYANGKUT' => 'float', 'UrutPO' => 'integer', 'HPP' => 'float', 'QntTerima' => 'float', 'Qnt1Terima' => 'float', 'Qnt2Terima' => 'float', 'QntReject' => 'float', 'Qnt1Reject' => 'float', 'Qnt2Reject' => 'float', 'HRGNETTO' => 'float', 'NDISKON' => 'float', 'SUBTOTAL' => 'float', 'SUBTOTALRp' => 'float', 'UrutBeli' => 'integer', 'DiscP2' => 'float', 'DiscP3' => 'float', 'DiscP4' => 'float', 'DiscP5' => 'float', 'PPhP' => 'float', 'NPPHRP' => 'float', 'NPPH' => 'float', 'PPnP' => 'float', 'NDPP' => 'float', 'NPPN' => 'float', 'NDPPRp' => 'float', 'NPPNRp' => 'float', 'NNET' => 'float', 'NNETRP' => 'float'];
}
