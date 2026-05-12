<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CUSTSUPP extends Model
{
    protected $table = 'DBCUSTSUPP';
    protected $fillable = ['KODECUSTSUPP', 'NAMACUSTSUPP', 'ALAMAT1', 'ALAMAT2', 'Kota', 'TELPON', 'FAX', 'EMAIL', 'KODEPOS', 'NEGARA', 'NPWP', 'Tanggal', 'PLAFON', 'HARI', 'HARIHUTPIUT', 'BERIKAT', 'USAHA', 'PERKIRAAN', 'JENIS', 'NAMAPKP', 'ALAMATPKP1', 'ALAMATPKP2', 'KOTAPKP', 'Sales', 'KodeVls', 'KodeExp', 'KodeTipe', 'IsPpn', 'IsAktif', 'Kind', 'ContactP', 'Alamat1ContP', 'Alamat2ContP', 'KotaContP', 'NegaraContP', 'TelpContP', 'FaxContP', 'EmailContP', 'KODEPOSContP', 'HPContP', 'SyaratPenerimaan', 'SyaratPembayaran', 'Agent', 'Alamat1A', 'Alamat2A', 'KotaA', 'NegaraA', 'ContactA', 'TelpA', 'FaxA', 'EmailA', 'KODEPOSA', 'HPA', 'EmailContA', 'MyID', 'PortOfLoading', 'CountryOfOrigin', 'TglInput', 'iskontrak', 'PPN', 'HargaKe', 'Att', 'bank', 'NoAcc', 'IsMember', 'TanggalValid', 'DiscMember', 'AttPhone', 'ket', 'JenisCustSupp', 'KODECUSTSUPPL', 'Tahun', 'IsUpdate'];
    protected $casts = ['PLAFON' => 'float', 'HARI' => 'integer', 'HARIHUTPIUT' => 'integer', 'BERIKAT' => 'boolean', 'JENIS' => 'integer', 'IsPpn' => 'boolean', 'IsAktif' => 'integer', 'Kind' => 'integer', 'iskontrak' => 'boolean', 'PPN' => 'integer', 'HargaKe' => 'integer', 'IsMember' => 'boolean', 'DiscMember' => 'float', 'Tahun' => 'integer', 'IsUpdate' => 'boolean'];
}
