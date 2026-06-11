<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Perkiraan rows for 1122.5 ===\n";
$rows = DB::connection('sqlsrv')->select(
    "SELECT Perkiraan, Keterangan FROM DBPERKIRAAN WHERE Perkiraan LIKE '1122.5%' ORDER BY Perkiraan"
);
foreach ($rows as $r) {
    echo "{$r->Perkiraan} | {$r->Keterangan}\n";
}

echo "\n=== Perkiraan 1122.5 (exact) ===\n";
$exact = DB::connection('sqlsrv')->selectOne(
    "SELECT Perkiraan, Keterangan FROM DBPERKIRAAN WHERE Perkiraan = '1122.5'"
);
print_r($exact);

echo "\n=== Sample transaksi for 1122.5 (exact) ===\n";
$tx = DB::connection('sqlsrv')->select(
    "SELECT TOP 5 Tanggal, NoBukti, Perkiraan, Lawan, Debet, TPHC FROM dbTransaksi WHERE Perkiraan = '1122.5' OR Lawan = '1122.5'"
);
foreach ($tx as $r) {
    print_r((array) $r);
}

echo "\n=== Sample transaksi for 1122.5% (children) ===\n";
$tx2 = DB::connection('sqlsrv')->select(
    "SELECT TOP 5 Tanggal, NoBukti, Perkiraan, Lawan, Debet, TPHC FROM dbTransaksi WHERE Perkiraan LIKE '1122.5%' OR Lawan LIKE '1122.5%'"
);
foreach ($tx2 as $r) {
    print_r((array) $r);
}
