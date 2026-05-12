<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HASILPRDDET extends Model
{
    protected $table = 'DBHASILPRDDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'KodeGdg', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'NoSPK', 'HPP', 'NPR1', 'NPR2', 'NPR3', 'NPR4', 'PR1', 'PR2', 'PR3', 'PR4', 'KodeMsn', 'tglHasilP', 'TglSpkMsn', 'isclosespk', 'TarifMesin', 'JamProduksi', 'JmlTarifPrd', 'urutmesin'];
    protected $casts = ['URUT' => 'integer', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HPP' => 'float', 'NPR1' => 'float', 'NPR2' => 'float', 'NPR3' => 'float', 'NPR4' => 'float', 'PR1' => 'float', 'PR2' => 'float', 'PR3' => 'float', 'PR4' => 'float', 'isclosespk' => 'boolean', 'TarifMesin' => 'float', 'JamProduksi' => 'float', 'JmlTarifPrd' => 'float', 'urutmesin' => 'integer'];
}
