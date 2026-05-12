<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'dbKaryawan';
    protected $fillable = ['KeyNIK', 'TipeTrans', 'NoBukti', 'NIK', 'Nama', 'NamaPanggilan', 'Kelamin', 'TmpLahir', 'TglLahir', 'Agama', 'Tinggi', 'Berat', 'BerkacaMata', 'Darah', 'NomorKTP', 'AlamatKTP', 'KecamatanKTP', 'KabupatenKTP', 'PropinsiKTP', 'KodePosKTP', 'AlamatRmh', 'KecamatanRmh', 'KabupatenRmh', 'PropinsiRmh', 'KodePosRmh', 'TeleponHP', 'KodePendAkhir', 'KetPendAkhir', 'StatusTempTinggal', 'Hubungan', 'Referensi', 'Rekomendasi', 'NamaR', 'JabatanR', 'NamaInstR', 'AlamatR', 'TglMasuk', 'TglKeluar', 'BankAccount', 'NomorAstek', 'TglAstek', 'KodeShf', 'KodeJab', 'KodeDept', 'KodeESL', 'KodeGrade', 'GajiPokok', 'TnjJabatan', 'TnjKehadiran', 'TnjTransport', 'TnjMakan', 'TnjLain2', 'TnjHaid', 'JKK', 'JHT', 'JPK', 'JKM', 'Prima', 'TnjPajak', 'StsPJK', 'StsAST', 'Tanggung', 'NPWP', 'Aktif', 'LamaKontrak', 'TglAkhirKontrak', 'IDUserInput', 'TglInput', 'IsSales', 'Produksi'];
    protected $casts = ['KeyNIK' => 'integer', 'Tinggi' => 'float', 'Berat' => 'float', 'BerkacaMata' => 'integer', 'Hubungan' => 'integer', 'Rekomendasi' => 'integer', 'GajiPokok' => 'float', 'TnjJabatan' => 'float', 'TnjKehadiran' => 'float', 'TnjTransport' => 'float', 'TnjMakan' => 'float', 'TnjLain2' => 'float', 'TnjHaid' => 'float', 'JKK' => 'float', 'JHT' => 'float', 'JPK' => 'float', 'JKM' => 'float', 'Prima' => 'float', 'TnjPajak' => 'boolean', 'Tanggung' => 'float', 'Aktif' => 'integer', 'LamaKontrak' => 'integer', 'IsSales' => 'boolean', 'Produksi' => 'integer'];
}
