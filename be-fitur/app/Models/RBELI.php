<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RBELI extends Model
{
    protected $table = 'DBRBELI';
    protected $fillable = ['NOBUKTI', 'NOURUT', 'TANGGAL', 'TGLJATUHTEMPO', 'KODESUPP', 'NOBELI', 'KodeGdg', 'KODEEXP', 'HANDLING', 'KETERANGAN', 'FAKTURSUPP', 'KODEVLS', 'KURS', 'PPN', 'TIPEBAYAR', 'HARI', 'TipeDisc', 'DISC', 'DISCRP', 'NILAIPOT', 'NILAIDPP', 'NILAIPPN', 'NILAINET', 'ISCETAK', 'NilaiCetak', 'Nopajak', 'TglFPJ', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'IsBatal', 'UserBatal', 'TglBatal', 'FlagTipe'];
    protected $casts = ['HANDLING' => 'float', 'KURS' => 'float', 'PPN' => 'integer', 'TIPEBAYAR' => 'integer', 'HARI' => 'integer', 'TipeDisc' => 'integer', 'DISC' => 'float', 'DISCRP' => 'float', 'NILAIPOT' => 'float', 'NILAIDPP' => 'float', 'NILAIPPN' => 'float', 'NILAINET' => 'float', 'ISCETAK' => 'boolean', 'NilaiCetak' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'IsBatal' => 'boolean', 'FlagTipe' => 'integer'];
}
