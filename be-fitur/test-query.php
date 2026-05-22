<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$count = DB::connection('sqlsrv')->select('SELECT COUNT(*) as cnt FROM dbmasterlaporan');
echo "dbmasterlaporan count: " . json_encode($count) . "\n";

$menus = DB::connection('sqlsrv')->select('SELECT COUNT(*) as cnt FROM DBMENUREPORT');
echo "DBMENUREPORT count: " . json_encode($menus) . "\n";

$reports = DB::connection('sqlsrv')->select('SELECT TOP 3 id_laporan, KODEMENU, nama_laporan FROM dbmasterlaporan');
echo "Reports: " . json_encode($reports) . "\n";