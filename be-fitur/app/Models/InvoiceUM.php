<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceUM extends Model
{
    protected $table = 'dbInvoiceUM';
    protected $fillable = ['NoBukti', 'NoUrut', 'Tanggal', 'DISC', 'PPN', 'Valas', 'Kurs', 'NoSPP', 'KodeCustSupp', 'Consignee', 'NotifyParty', 'StuffingDate', 'StuffingPlace', 'ContractNo', 'PONo', 'PaymentTerm', 'DocCreditNo', 'PoL', 'PoD', 'NameOfVessel', 'Feeder_Vessel', 'Connect_Vessel', 'ShipOnBoardDate', 'Packing', 'Others', 'IsCetak', 'IDUser', 'IsLokal', 'NoBL', 'NoteBeneficiary1', 'NoteBeneficiary2', 'NoteBeneficiary3', 'ShipmentAdvice1', 'ShipmentAdvice2', 'ETADestination', 'ToShipmentAdvice2', 'NoPajak', 'TglFPJ', 'Footnote', 'IssuingBank', 'MyID', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'IsFLag', 'MAXOL', 'NoKMK', 'TglKMK', 'IsBatal', 'UserBatal', 'TglBatal', 'FlagTipe', 'DP'];
    protected $casts = ['DISC' => 'float', 'PPN' => 'integer', 'Kurs' => 'float', 'IsCetak' => 'boolean', 'IsLokal' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'IsFLag' => 'boolean', 'MAXOL' => 'integer', 'IsBatal' => 'boolean', 'FlagTipe' => 'integer', 'DP' => 'float'];
}
