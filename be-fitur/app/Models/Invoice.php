<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'DBInvoice';
    protected $fillable = ['NOBUKTI', 'TANGGAL', 'KETERANGAN', 'KodeSupp', 'NoPO', 'NoFaktur', 'TglFaktur', 'KodeVls', 'Kurs', 'PPN', 'TipeBayar', 'Hari', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'NOURUT', 'Flagtipe'];
    protected $casts = ['Kurs' => 'float', 'PPN' => 'integer', 'TipeBayar' => 'integer', 'Hari' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'Flagtipe' => 'integer'];
}
