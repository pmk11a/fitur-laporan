<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// Test Sp_LapSaldoAwal manually for perk 2101.1, date 2019-10-23, divisi 01
echo "=== dbNeraca sample for 2101.1 ===\n";
$rows = DB::connection('sqlsrv')->select("
    SELECT TOP 5 * FROM dbNeraca WHERE Perkiraan = '2101.1'
    ORDER BY Tahun DESC, Bulan DESC
");
foreach ($rows as $r) {
    $a = (array)$r;
    echo "  " . json_encode($a, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== dbBon sample for 2101.1 ===\n";
$rows = DB::connection('sqlsrv')->select("
    SELECT TOP 5 * FROM dbBon WHERE Perkiraan = '2101.1'
    ORDER BY Tanggal DESC
");
foreach ($rows as $r) {
    $a = (array)$r;
    echo "  " . json_encode($a, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== dbGiro sample ===\n";
$rows = DB::connection('sqlsrv')->select("
    SELECT TOP 3 * FROM dbGiro ORDER BY TglBuka DESC
");
foreach ($rows as $r) {
    $a = (array)$r;
    echo "  " . json_encode($a, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== dbNeraca columns ===\n";
$first = DB::connection('sqlsrv')->select("SELECT TOP 1 * FROM dbNeraca");
echo implode(', ', array_keys((array)$first[0])) . "\n";
