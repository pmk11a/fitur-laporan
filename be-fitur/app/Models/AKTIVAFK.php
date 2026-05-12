<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AKTIVAFK extends Model
{
    protected $table = 'DBAKTIVAFK';
    protected $fillable = ['Devisi', 'Perkiraan', 'Keterangan', 'Quantity', 'Persen', 'Tanggal', 'Tipe', 'Kodebag', 'Akumulasi', 'NoMuka', 'NoBelakang', 'Biaya', 'PersenBiaya1', 'Biaya2', 'PersenBiaya2', 'biaya3', 'persenbiaya3', 'biaya4', 'persenbiaya4', 'TipeAktiva', 'NoBelakang2', 'NoAktivaHd', 'Kelompok', 'GroupAktiva'];
    protected $casts = ['Quantity' => 'float', 'Persen' => 'float', 'PersenBiaya1' => 'float', 'PersenBiaya2' => 'float', 'persenbiaya3' => 'float', 'persenbiaya4' => 'float', 'TipeAktiva' => 'integer', 'Kelompok' => 'integer'];
}
