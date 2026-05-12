<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HUTPIUT extends Model
{
    protected $table = 'DBHUTPIUT';
    protected $fillable = ['NoFaktur', 'NoRetur', 'TipeTrans', 'KodeCustSupp', 'NoBukti', 'NoMsk', 'Urut', 'Tanggal', 'JatuhTempo', 'Debet', 'Kredit', 'Saldo', 'Valas', 'Kurs', 'DebetD', 'KreditD', 'SaldoD', 'KodeSales', 'Tipe', 'Perkiraan', 'Catatan', 'MyID', 'NOINVOICE', 'TGLINVOICE', 'NOPAJAK', 'TGLFPJ', 'KodeVls_', 'Kurs_', 'KursBayar', 'FlagSimbol', 'Tipebayar', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'IsClose', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'NOSO', 'NOSPB', 'KodeBrgCust', 'isClosing'];
    protected $casts = ['NoMsk' => 'integer', 'Urut' => 'integer', 'Debet' => 'float', 'Kredit' => 'float', 'Saldo' => 'float', 'Kurs' => 'float', 'DebetD' => 'float', 'KreditD' => 'float', 'SaldoD' => 'float', 'Kurs_' => 'float', 'KursBayar' => 'float', 'Tipebayar' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'IsClose' => 'boolean', 'MaxOL' => 'integer', 'isClosing' => 'boolean'];
}
