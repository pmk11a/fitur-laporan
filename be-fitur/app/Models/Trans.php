<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trans extends Model
{
    protected $table = 'dbTrans';
    protected $fillable = ['NoBukti', 'NOURUT', 'Tanggal', 'Note', 'Lampiran', 'MyID', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'Simbol', 'TipeTransHd', 'PerkiraanHd', 'FlagSimbol', 'MaxOL', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'Flagtipe'];
    protected $casts = ['Lampiran' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'Flagtipe' => 'integer'];
}
