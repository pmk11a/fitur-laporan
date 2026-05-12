<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RInvoicePL extends Model
{
    protected $table = 'DBRInvoicePL';
    protected $fillable = ['NOBUKTI', 'NOURUT', 'TANGGAL', 'TGLJATUHTEMPO', 'KODECUSTSUPP', 'NoInvoice', 'TglInvoice', 'NoSO', 'TglSO', 'NoSPP', 'TglSPP', 'NOSPB', 'TGLSPB', 'KODEVLS', 'KURS', 'PPN', 'TIPEBAYAR', 'HARI', 'Tipe', 'DISC', 'DISCRP', 'NILAIPOT', 'NILAIDPP', 'NILAIPPN', 'NILAINET', 'NILAIPOTRp', 'NILAIDPPRp', 'NILAIPPNRp', 'NILAINETRp', 'FREIGHT', 'LAIN2', 'ISCETAK', 'ISCETAKGDG', 'ISBATAL', 'USERBATAL', 'IDUser', 'FlagRetur', 'IsLokal', 'MyID', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'IsFLag', 'NoLKP', 'TGLLKP', 'MaxOL', 'TglBatal'];
    protected $casts = ['KURS' => 'float', 'PPN' => 'integer', 'TIPEBAYAR' => 'integer', 'HARI' => 'integer', 'Tipe' => 'integer', 'DISC' => 'float', 'DISCRP' => 'float', 'NILAIPOT' => 'float', 'NILAIDPP' => 'float', 'NILAIPPN' => 'float', 'NILAINET' => 'float', 'NILAIPOTRp' => 'float', 'NILAIDPPRp' => 'float', 'NILAIPPNRp' => 'float', 'NILAINETRp' => 'float', 'FREIGHT' => 'float', 'LAIN2' => 'float', 'ISCETAK' => 'integer', 'ISCETAKGDG' => 'integer', 'ISBATAL' => 'boolean', 'FlagRetur' => 'integer', 'IsLokal' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'IsFLag' => 'boolean', 'MaxOL' => 'integer'];
}
