<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MasterLaporan extends Model
{
    protected $table = 'dbmasterlaporan';
    protected $primaryKey = 'id_laporan';
    public $timestamps = false;

    protected $fillable = [
        'KODEMENU',
        'nama_laporan',
        'deskripsi',
        'query_sumber_data',
        'status_aktif',
        'footer_bands'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'footer_bands' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (MasterLaporan $report) {
            $id = $report->id_laporan;
            DB::connection('sqlsrv')->table('dbparameterlaporan')->where('id_laporan', $id)->delete();
            DB::connection('sqlsrv')->table('dbquerylaporan')->where('id_laporan', $id)->delete();
            DB::connection('sqlsrv')->table('dbkolomlaporan')->where('id_laporan', $id)->delete();
            DB::connection('sqlsrv')->table('dbgrouplaporan')->where('id_laporan', $id)->delete();
        });
    }

    public function parameters()
    {
        return $this->hasMany(ParameterLaporan::class, 'id_laporan', 'id_laporan')
            ->orderBy('posisi');
    }

    public function menu()
    {
        return $this->belongsTo(DBMENUREPORT::class, 'KODEMENU', 'KODEMENU');
    }
}