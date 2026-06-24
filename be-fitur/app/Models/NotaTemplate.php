<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaTemplate extends Model
{
    protected $table = 'dbnotatemplate';
    protected $primaryKey = 'id_template';
    public $timestamps = false;

    protected $fillable = [
        'kode_nota',
        'nama_nota',
        'paper_size',
        'orientation',
        'margins',
        'font_family',
        'font_size',
        'config_json',
        'query_header',
        'query_detail',
        'query_params',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'config_json' => 'array',
    ];
}
