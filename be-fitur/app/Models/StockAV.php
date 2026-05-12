<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAV extends Model
{
    protected $table = 'DBStockAV';
    protected $fillable = ['Bulan', 'Tahun', 'Kodebrg', 'Kodegdg', 'QntAwal', 'Qnt2Awal', 'QntIN', 'Qnt2IN', 'QntOut', 'Qnt2Out', 'QntSPP', 'Qnt2SPP', 'SaldoQnt', 'Saldo2Qnt'];
    protected $casts = ['Bulan' => 'integer', 'Tahun' => 'integer', 'QntAwal' => 'float', 'Qnt2Awal' => 'float', 'QntIN' => 'float', 'Qnt2IN' => 'float', 'QntOut' => 'float', 'Qnt2Out' => 'float', 'QntSPP' => 'float', 'Qnt2SPP' => 'float', 'SaldoQnt' => 'float', 'Saldo2Qnt' => 'float'];
}
