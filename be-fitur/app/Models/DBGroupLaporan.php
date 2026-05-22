<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DBGroupLaporan extends Model
{
    protected $table = 'dbgrouplaporan';
    protected $primaryKey = 'id_group';
    public $timestamps = false;

    protected $fillable = [
        'id_laporan',
        'group_level',
        'group_field',
        'field_value',
        'label',
        'sort_order',
        'show_subtotal',
        'style_config',
        'special_handling',
        'config_json',
        'deskripsi'
    ];

    protected $casts = [
        'group_level' => 'integer',
        'sort_order' => 'integer',
        'show_subtotal' => 'boolean'
    ];
}