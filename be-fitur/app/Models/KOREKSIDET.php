<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KOREKSIDET extends Model
{
    protected $table = 'DBKOREKSIDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'KODEGDG', 'SATUAN', 'NOSAT', 'ISI', 'SaldoComp', 'QntOpname', 'Selisih', 'QNTDB', 'QNTCR', 'HARGA', 'HPP', 'keterangan', 'Saldo2Comp', 'Qnt2Opname', 'Selisih2', 'Qnt2DB', 'Qnt2CR', 'IsCek', 'IsCek2'];
    protected $casts = ['URUT' => 'integer', 'NOSAT' => 'integer', 'ISI' => 'float', 'SaldoComp' => 'float', 'QntOpname' => 'float', 'Selisih' => 'float', 'QNTDB' => 'float', 'QNTCR' => 'float', 'HARGA' => 'float', 'HPP' => 'float', 'Saldo2Comp' => 'float', 'Qnt2Opname' => 'float', 'Selisih2' => 'float', 'Qnt2DB' => 'float', 'Qnt2CR' => 'float', 'IsCek' => 'boolean', 'IsCek2' => 'boolean'];
}
