<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class STOCKBRG extends Model
{
    protected $table = 'DBSTOCKBRG';
    protected $fillable = ['BULAN', 'TAHUN', 'KODEBRG', 'KODEGDG', 'QNTAWAL', 'QNT2AWAL', 'HRGAWAL', 'QNTPBL', 'QNT2PBL', 'HRGPBL', 'QNTRPB', 'QNT2RPB', 'HRGRPB', 'QNTPNJ', 'QNT2PNJ', 'HRGPNJ', 'QNTRPJ', 'QNT2RPJ', 'HRGRPJ', 'QNTPRJ', 'HRGPRJ', 'QNTADI', 'QNT2ADI', 'HRGADI', 'QNTADO', 'QNT2ADO', 'HRGADO', 'QNTUKI', 'QNT2UKI', 'HRGUKI', 'QNTUKO', 'QNT2UKO', 'HRGUKO', 'QNTTRI', 'HRGTRI', 'QNT2TRI', 'QNTTRO', 'QNT2TRO', 'HRGTRO', 'QNTPMK', 'QNT2PMK', 'HRGPMK', 'QNTRPK', 'QNT2RPK', 'HRGRPK', 'QntHPrd', 'Qnt2HPrd', 'HRGHPrd', 'HRGRATA', 'QNTIN', 'QNT2IN', 'RPIN', 'QNTOUT', 'QNT2OUT', 'RPOUT', 'SALDOQNT', 'SALDO2QNT', 'SALDORP', 'SaldoAV', 'Saldo2AV'];
    protected $casts = ['BULAN' => 'integer', 'TAHUN' => 'integer', 'QNTAWAL' => 'float', 'QNT2AWAL' => 'float', 'HRGAWAL' => 'float', 'QNTPBL' => 'float', 'QNT2PBL' => 'float', 'HRGPBL' => 'float', 'QNTRPB' => 'float', 'QNT2RPB' => 'float', 'HRGRPB' => 'float', 'QNTPNJ' => 'float', 'QNT2PNJ' => 'float', 'HRGPNJ' => 'float', 'QNTRPJ' => 'float', 'QNT2RPJ' => 'float', 'HRGRPJ' => 'float', 'QNTPRJ' => 'float', 'HRGPRJ' => 'float', 'QNTADI' => 'float', 'QNT2ADI' => 'float', 'HRGADI' => 'float', 'QNTADO' => 'float', 'QNT2ADO' => 'float', 'HRGADO' => 'float', 'QNTUKI' => 'float', 'QNT2UKI' => 'float', 'HRGUKI' => 'float', 'QNTUKO' => 'float', 'QNT2UKO' => 'float', 'HRGUKO' => 'float', 'QNTTRI' => 'float', 'HRGTRI' => 'float', 'QNT2TRI' => 'float', 'QNTTRO' => 'float', 'QNT2TRO' => 'float', 'HRGTRO' => 'float', 'QNTPMK' => 'float', 'QNT2PMK' => 'float', 'HRGPMK' => 'float', 'QNTRPK' => 'float', 'QNT2RPK' => 'float', 'HRGRPK' => 'float', 'QntHPrd' => 'float', 'Qnt2HPrd' => 'float', 'HRGHPrd' => 'float', 'HRGRATA' => 'float', 'QNTIN' => 'float', 'QNT2IN' => 'float', 'RPIN' => 'float', 'QNTOUT' => 'float', 'QNT2OUT' => 'float', 'RPOUT' => 'float', 'SALDOQNT' => 'float', 'SALDO2QNT' => 'float', 'SALDORP' => 'float', 'SaldoAV' => 'integer', 'Saldo2AV' => 'integer'];
}
