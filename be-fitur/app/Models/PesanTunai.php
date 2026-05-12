<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesanTunai extends Model
{
    protected $table = 'dbPesanTunai';
    protected $fillable = ['NOBUKTI', 'NoUrut', 'TANGGAL', 'KODECUST', 'ISCETAK', 'BayarTunai', 'BayarDebet', 'NoDebet', 'BankDebet', 'BayarKredit', 'TipeKartuKredit', 'NoKredit', 'BankKredit', 'BayarVoucher', 'VoucherRp', 'TglInput', 'UserID', 'DiscMember', 'DiscHarian', 'Keterangan', 'KodeRekan', 'NoKartuRekan', 'DiscRekan', 'Pemesan', 'IsOrder', 'IsAmbil', 'Alamat', 'Telepon', 'TanggalAmbil', 'DP', 'KodeGdg', 'TglKirim', 'JamKirim', 'Piutang', 'IsAmbilBrg'];
    protected $casts = ['NoUrut' => 'integer', 'ISCETAK' => 'boolean', 'BayarTunai' => 'float', 'BayarDebet' => 'float', 'BayarKredit' => 'float', 'TipeKartuKredit' => 'integer', 'BayarVoucher' => 'float', 'VoucherRp' => 'float', 'DiscMember' => 'float', 'DiscHarian' => 'float', 'DiscRekan' => 'float', 'IsOrder' => 'boolean', 'IsAmbil' => 'boolean', 'DP' => 'float', 'Piutang' => 'float', 'IsAmbilBrg' => 'boolean'];
}
