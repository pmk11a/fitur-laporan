<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RSPB extends Model
{
    protected $table = 'DBRSPB';
    protected $fillable = ['NoBukti', 'NoUrut', 'Tanggal', 'NoSPB', 'KodeCustSupp', 'NoPolKend', 'Container', 'NoContainer', 'NoSeal', 'Catatan', 'IsCetak', 'IDUser', 'MyID', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'IsEkstern', 'CustAngkutan', 'IsFlag', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'KodeGdg', 'MaxOL', 'Flagtipe'];
    protected $casts = ['IsCetak' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'IsEkstern' => 'boolean', 'IsFlag' => 'boolean', 'MaxOL' => 'integer', 'Flagtipe' => 'integer'];
}
