<?php
require __DIR__.'/be-fitur/vendor/autoload.php';
$app = require_once __DIR__.'/be-fitur/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== dbparameterlaporan (id_laporan=115) ===\n";
$rows = DB::table('dbparameterlaporan')->where('id_laporan', 115)->orderBy('id_parameter')->get();
foreach ($rows as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}
echo "\n=== count: " . count($rows) . " ===\n";

$count = DB::connection('sqlsrv')->select('SELECT COUNT(*) as cnt FROM dbmasterlaporan');
echo "dbmasterlaporan count: " . json_encode($count) . "\n";

$menus = DB::connection('sqlsrv')->select('SELECT COUNT(*) as cnt FROM DBMENUREPORT');
echo "DBMENUREPORT count: " . json_encode($menus) . "\n";

$reports = DB::connection('sqlsrv')->select('SELECT TOP 3 id_laporan, KODEMENU, nama_laporan FROM dbmasterlaporan');
echo "Reports: " . json_encode($reports) . "\n";