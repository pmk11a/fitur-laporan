<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JualTunai extends Model
{
    protected $table = 'dbJualTunai';
    protected $fillable = ['NOBUKTI', 'NoUrut', 'TANGGAL', 'KODECUST', 'ISCETAK', 'BayarTunai', 'BayarDebet', 'NoDebet', 'BankDebet', 'BayarKredit', 'TipeKartuKredit', 'NoKredit', 'BankKredit', 'BayarVoucher', 'VoucherRp', 'TglInput', 'UserID', 'DiscMember', 'DiscHarian', 'Keterangan', 'KodeRekan', 'NoKartuRekan', 'DiscRekan', 'Pemesan', 'IsOrder', 'Alamat', 'Telepon', 'TanggalAmbil', 'DP', 'Piutang', 'Tunai', 'Potongan', 'Debit', 'BankDebit', 'NoKartuDebit', 'CC', 'TipeCC', 'BankCC', 'NoCC', 'Voucher', 'Kembali', 'KodePiutCustomer', 'TGLJATUHTEMPO'];
    protected $casts = ['NoUrut' => 'integer', 'ISCETAK' => 'boolean', 'BayarTunai' => 'float', 'BayarDebet' => 'float', 'BayarKredit' => 'float', 'TipeKartuKredit' => 'integer', 'BayarVoucher' => 'float', 'VoucherRp' => 'float', 'DiscMember' => 'float', 'DiscHarian' => 'float', 'DiscRekan' => 'float', 'IsOrder' => 'boolean', 'DP' => 'float', 'Piutang' => 'float', 'Tunai' => 'float', 'Potongan' => 'float', 'Debit' => 'float', 'CC' => 'float', 'TipeCC' => 'integer', 'Voucher' => 'float', 'Kembali' => 'float'];
}
