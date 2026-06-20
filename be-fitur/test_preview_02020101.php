<?php
require dirname(__DIR__).'/be-fitur/vendor/autoload.php';
$app = require dirname(__DIR__).'/be-fitur/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Services\ReportService;

$svc = app(ReportService::class);

echo "=== Preview 02020101 (BKM, Devisi=01, March 2022) ===\n";
$result = $svc->generateReport('02020101', [
    'Devisi' => '01',
    'TglAwal' => '2022-03-01',
    'TglAkhir' => '2022-03-31',
]);

if (!$result['success']) {
    echo "ERROR: " . ($result['message'] ?? 'unknown') . "\n";
    exit(1);
}

foreach ($result['datasets'] as $name => $rows) {
    echo "Dataset: $name, rows: " . count($rows) . "\n";
    foreach (array_slice($rows, 0, 3) as $i => $r) {
        echo "  Row $i:\n";
        foreach ($r as $k => $v) {
            $t = is_null($v) ? 'NULL' : gettype($v);
            $val = is_string($v) ? '"'.substr($v,0,60).'"' : ($v ?? 'NULL');
            echo "    $k = $val  (type=$t)\n";
        }
    }
}