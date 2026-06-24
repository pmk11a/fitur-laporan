<?php

use Illuminate\Support\Facades\Route;

use App\Models\ParameterLaporan;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug-reports/{id_laporan}', function ($id_laporan) {
    $params = ParameterLaporan::where('id_laporan', $id_laporan)->get(['id_parameter', 'nama_filter', 'label', 'tipe_input', 'konfigurasi']);
    return response()->json([
        'id_laporan' => $id_laporan,
        'count' => $params->count(),
        'records' => $params->map(function ($p) {
            $cfg = null;
            if ($p->konfigurasi) {
                try {
                    $cfg = json_decode($p->konfigurasi, true);
                } catch (\Exception $e) {
                    $cfg = ['_raw' => $p->konfigurasi];
                }
            }
            return [
                'id_parameter' => $p->id_parameter,
                'nama_filter' => $p->nama_filter,
                'label' => $p->label,
                'tipe_input' => $p->tipe_input,
                'konfigurasi_parsed' => $cfg,
                'konfigurasi_raw' => $p->konfigurasi,
            ];
        }),
    ]);
});
