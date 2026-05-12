<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SORevisi extends Model
{
    protected $table = 'DBSORevisi';
    protected $fillable = ['NOBUKTI', 'RevisiKe', 'NOURUT', 'TANGGAL', 'TGLJATUHTEMPO', 'KODECUST', 'NOSPB', 'NoAlamatKirim', 'AlamatKirim', 'HANDLING', 'KODESLS', 'KETERANGAN', 'KODEVLS', 'KURS', 'PPN', 'TIPEBAYAR', 'HARI', 'CATATAN', 'TIPEDISC', 'DISC', 'DISCRP', 'NILAIPOT', 'NILAIDPP', 'NILAIPPN', 'NILAINET', 'ISCETAK', 'ISBATAL', 'USERBATAL', 'KODEGDG', 'KodeExp', 'INSGdg', 'INSBrg', 'Jam', 'NewNo', 'FLAGTIPE', 'NOPI', 'TIPESC', 'TERM1P', 'TERM1VLS', 'TERM1KURS', 'TERM1KET', 'TERM2P', 'TERM2VLS', 'TERM2KURS', 'TERM2KET', 'TERM3P', 'TERM3VLS', 'TERM3KURS', 'TERM3KET', 'TERM4P', 'TERM4VLS', 'TERM4KURS', 'TERM4KET', 'TERM5P', 'TERM5VLS', 'TERM5KURS', 'TERM5KET', 'KetTipeEkspor', 'IsLengkap', 'userid', 'TglInput', 'NoPesanan', 'TglKirim', 'MasaBerlaku', 'IsOtorisasi1', 'OtoUser1', 'TglOto1', 'IsOtorisasi2', 'OtoUser2', 'TglOto2', 'IsOtorisasi3', 'OtoUser3', 'TglOto3', 'IsOtorisasi4', 'OtoUser4', 'TglOto4', 'IsOtorisasi5', 'OtoUser5', 'TglOto5', 'NoJurnal', 'NoUrutJurnal', 'TglJurnal', 'cetakke', 'MAXOL', 'tglRevisi'];
    protected $casts = ['RevisiKe' => 'integer', 'NoAlamatKirim' => 'integer', 'HANDLING' => 'float', 'KODESLS' => 'integer', 'KURS' => 'float', 'PPN' => 'integer', 'TIPEBAYAR' => 'integer', 'HARI' => 'integer', 'TIPEDISC' => 'integer', 'DISC' => 'float', 'DISCRP' => 'float', 'NILAIPOT' => 'float', 'NILAIDPP' => 'float', 'NILAIPPN' => 'float', 'NILAINET' => 'float', 'ISCETAK' => 'integer', 'ISBATAL' => 'boolean', 'TIPESC' => 'integer', 'TERM1P' => 'float', 'TERM1KURS' => 'float', 'TERM2P' => 'float', 'TERM2KURS' => 'float', 'TERM3P' => 'float', 'TERM3KURS' => 'float', 'TERM4P' => 'float', 'TERM4KURS' => 'float', 'TERM5P' => 'float', 'TERM5KURS' => 'float', 'IsLengkap' => 'boolean', 'IsOtorisasi1' => 'boolean', 'IsOtorisasi2' => 'boolean', 'IsOtorisasi3' => 'boolean', 'IsOtorisasi4' => 'boolean', 'IsOtorisasi5' => 'boolean', 'cetakke' => 'integer', 'MAXOL' => 'integer'];
}
