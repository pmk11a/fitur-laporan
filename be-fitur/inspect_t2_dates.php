<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

// Find rows that match the sample saldo+debet pattern
echo "=== rows with Debet=12500000 (any date) for perk 2101.1 ===\n";
$rows = DB::connection('sqlsrv')->select("
    SELECT TOP 5 * FROM dbTransaksi
    WHERE Perkiraan = '2101.1' AND Debet = 12500000
");
foreach ($rows as $r) {
    $a = (array)$r;
    echo "  NoBukti: {$a['NoBukti']} | Tanggal: {$a['Tanggal']} | Debet: {$a['Debet']} | Kredit: {$a['Kredit']} | Devisi: {$a['Devisi']}\n";
}

echo "\n=== rows for perk 2101.1, any date, latest first ===\n";
$rows = DB::connection('sqlsrv')->select("
    SELECT TOP 5 NoBukti, Tanggal, Devisi, Perkiraan, Lawan, Debet, Kredit
    FROM dbTransaksi
    WHERE Perkiraan = '2101.1'
    ORDER BY Tanggal DESC
");
foreach ($rows as $r) {
    $a = (array)$r;
    echo "  " . implode(' | ', $a) . "\n";
}

echo "\n=== saldo for perk 2101.1 (search dbPerkiraan/COA) ===\n";
$first = DB::connection('sqlsrv')->select("SELECT TOP 1 * FROM dbPerkiraan");
echo "Columns dbPerkiraan: " . implode(', ', array_keys((array)$first[0])) . "\n";

$rows = DB::connection('sqlsrv')->select("SELECT * FROM dbPerkiraan WHERE IDPERKIRAAN LIKE '2101%'");
foreach ($rows as $r) {
    $a = (array)$r;
    echo "  " . json_encode($a, JSON_UNESCAPED_UNICODE) . "\n";
}
