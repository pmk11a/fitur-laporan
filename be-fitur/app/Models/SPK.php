<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SPK extends Model
{
    protected $table = 'DBSPK';
    protected $fillable = ['NOBUKTI', 'NoUrut', 'TANGGAL', 'KODEBRG', 'NoBatch', 'TglExpired', 'Qnt', 'IsCLose', 'Nosat', 'Satuan', 'Isi', 'KodeBOM', 'CetakKe', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'MaxOL', 'NoSO', 'UrutSO', 'BiayaLain', 'TglSelesai', 'QntCetak', 'JenisSpk'];
    protected $casts = ['Qnt' => 'float', 'IsCLose' => 'boolean', 'Nosat' => 'integer', 'Isi' => 'float', 'CetakKe' => 'integer', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'MaxOL' => 'integer', 'UrutSO' => 'integer', 'BiayaLain' => 'float', 'QntCetak' => 'float', 'JenisSpk' => 'integer'];
}
