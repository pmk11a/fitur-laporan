<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempBKBesar extends Model
{
    protected $table = 'dbTempBKBesar';
    protected $fillable = ['Devisi', 'NoACC', 'NamaACC', 'Transaksi', 'NoBukti', 'Tanggal', 'Keterangan', 'Perkiraan', 'Lawan', 'Debet', 'Kredit', 'Saldo', 'SaldoAwal', 'Bulan', 'Tahun', 'Urut', 'DebetD', 'KreditD', 'SaldoAwalD', 'Valas', 'Kurs'];
    protected $casts = ['Debet' => 'float', 'Kredit' => 'float', 'Saldo' => 'float', 'SaldoAwal' => 'float', 'Bulan' => 'integer', 'Tahun' => 'integer', 'Urut' => 'integer', 'DebetD' => 'float', 'KreditD' => 'float', 'SaldoAwalD' => 'float', 'Kurs' => 'float'];
}
