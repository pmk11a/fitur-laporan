<?php
require __DIR__.'/vendor/autoload.php';
$app = new Illuminate\Foundation\Application(__DIR__);
$app->singleton(Illuminate\Contracts\Http\Kernel::class, App\Http\Kernel::class);
$app->singleton(Illuminate\Contracts\Console\Kernel::class, class_exists('App\Console\Kernel') ? 'App\Console\Kernel' : function() {});
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class, 'App\Exceptions\Handler');

$app->singleton(Illuminate\Support\Facades\Console::class, function($app) { return new Illuminate\Support\Facades\Console(); });

// Directly configure the DB connection without full bootstrap
$app->instance('config', new Illuminate\Config\Repository([
    'database' => require __DIR__.'/config/database.php',
]));

$container = $app;

// Use PDO directly for SQL Server
$cfg = require __DIR__.'/config/database.php';
$default = $cfg['default'];
$conn = $cfg['connections'][$default];
$dns = "sqlsrv:Server={$conn['host']};Database={$conn['database']};";
$pdo = new PDO($dns, $conn['username'], $conn['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

echo "=== Sp_LapJurnal sample ===\n";
$stmt = $pdo->prepare("EXEC Sp_LapJurnal ?, ?, ?, ?");
$stmt->execute(['BKM', '01', '2022-01-01', '2022-12-31']);
$rows = $stmt->fetchAll(PDO::FETCH_OBJ);
echo "Total rows: ".count($rows)."\n\n";
foreach (array_slice($rows, 0, 5) as $i => $r) {
    echo "Row $i:\n";
    foreach ((array) $r as $k => $v) {
        $t = is_null($v) ? 'NULL' : gettype($v);
        echo "  $k = ".var_export($v, true)." ($t)\n";
    }
}
