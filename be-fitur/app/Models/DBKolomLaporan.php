<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBKolomLaporan extends Model
{
    protected $table = 'dbkolomlaporan';
    protected $primaryKey = 'id_kolom';
    public $timestamps = false;

    protected $fillable = [
        'id_laporan',
        'nama_dataset',
        'nama_kolom',
        'label_tampil',
        'urutan_tampil',
        'format_type',
        'alignment',
        'is_summable',
        'is_visible',
        'deskripsi'
    ];

    protected $casts = [
        'urutan_tampil' => 'integer',
        'is_summable' => 'boolean',
        'is_visible' => 'boolean'
    ];
}