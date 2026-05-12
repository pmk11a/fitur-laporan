<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PRODUKSIDET extends Model
{
    protected $table = 'DBPRODUKSIDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'NoSPK', 'HPP', 'NPR1', 'NPR2', 'NPR3', 'NPR4', 'PR1', 'PR2', 'PR3', 'PR4', 'KodeMsn', 'tglHasilP', 'TglSpkMsn', 'isclosespk', 'TarifMesin', 'urutmesin', 'PR5', 'PR6', 'QntAktual', 'Keterangan', 'NPR5', 'PR7', 'PR8', 'JamProduksi', 'JmlTarifPrd', 'Lintasan', 'HasilBaik', 'HasilRusak'];
    protected $casts = ['URUT' => 'integer', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HPP' => 'float', 'NPR1' => 'float', 'NPR2' => 'float', 'NPR3' => 'float', 'NPR4' => 'float', 'PR1' => 'float', 'PR2' => 'float', 'PR3' => 'float', 'PR4' => 'float', 'isclosespk' => 'boolean', 'TarifMesin' => 'float', 'urutmesin' => 'integer', 'PR5' => 'float', 'PR6' => 'float', 'QntAktual' => 'float', 'NPR5' => 'float', 'PR7' => 'float', 'PR8' => 'float', 'JamProduksi' => 'float', 'JmlTarifPrd' => 'float', 'Lintasan' => 'integer', 'HasilBaik' => 'float', 'HasilRusak' => 'float'];
}
