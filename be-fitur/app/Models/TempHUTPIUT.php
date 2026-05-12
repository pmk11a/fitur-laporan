<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempHUTPIUT extends Model
{
    protected $table = 'DBTempHUTPIUT';
    protected $fillable = ['NoFaktur', 'NoRetur', 'TipeTrans', 'KodeCustSupp', 'NoBukti', 'NoMsk', 'Urut', 'Tanggal', 'JatuhTempo', 'Debet', 'Kredit', 'Saldo', 'Valas', 'Kurs', 'DebetD', 'KreditD', 'SaldoD', 'KodeSales', 'Tipe', 'Perkiraan', 'Catatan', 'MyID', 'IDUser', 'StatusUID', 'JumlahSaldo', 'JumlahSaldoD', 'TipeDK', 'NoInvoice', 'Valas_', 'Kurs_', 'KursBayar', 'NOSO', 'NOSPB', 'KodeBrgCust'];
    protected $casts = ['NoMsk' => 'integer', 'Urut' => 'integer', 'Debet' => 'float', 'Kredit' => 'float', 'Saldo' => 'float', 'Kurs' => 'float', 'DebetD' => 'float', 'KreditD' => 'float', 'SaldoD' => 'float', 'JumlahSaldo' => 'float', 'JumlahSaldoD' => 'float', 'Kurs_' => 'float', 'KursBayar' => 'float'];
}
