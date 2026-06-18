<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$id = 97;

echo "BEFORE:\n";
$cols = DB::connection('sqlsrv')->select("SELECT id_kolom, nama_kolom, label_tampil FROM dbkolomlaporan WHERE id_laporan = ? AND nama_dataset='QuView1' AND nama_kolom IN ('Saldo','SaldoAkhir','saldo','saldoakhir')", [$id]);
foreach ($cols as $c) echo "  id={$c->id_kolom} nama_kolom='{$c->nama_kolom}' label='{$c->label_tampil}'\n";

// Update
$updated = DB::connection('sqlsrv')->update(
    "UPDATE dbkolomlaporan SET nama_kolom = 'SaldoAkhir' WHERE id_laporan = ? AND nama_dataset = 'QuView1' AND nama_kolom = 'Saldo'",
    [$id]
);
echo "\nRows updated: $updated\n";

echo "\nAFTER:\n";
$cols = DB::connection('sqlsrv')->select("SELECT id_kolom, nama_kolom, label_tampil FROM dbkolomlaporan WHERE id_laporan = ? AND nama_dataset='QuView1' AND nama_kolom LIKE '%aldo%'", [$id]);
foreach ($cols as $c) echo "  id={$c->id_kolom} nama_kolom='{$c->nama_kolom}' label='{$c->label_tampil}'\n";
?>
