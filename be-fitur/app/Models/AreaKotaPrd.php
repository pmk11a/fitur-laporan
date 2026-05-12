<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaKotaPrd extends Model
{
    protected $table = 'DBAreaKotaPrd';
    protected $fillable = ['KodeArea', 'KodeKota', 'KodeBrg', 'Tahun', 'QntBln1', 'Qnt2Bln1', 'RpBln1', 'QntBln2', 'Qnt2Bln2', 'RpBln2', 'QntBln3', 'Qnt2Bln3', 'RpBln3', 'QntBln4', 'Qnt2Bln4', 'RpBln4', 'QntBln5', 'Qnt2Bln5', 'RpBln5', 'QntBln6', 'Qnt2Bln6', 'RpBln6', 'QntBln7', 'Qnt2Bln7', 'RpBln7', 'QntBln8', 'Qnt2Bln8', 'RpBln8', 'QntBln9', 'Qnt2Bln9', 'RpBln9', 'QntBln10', 'Qnt2Bln10', 'RpBln10', 'QntBln11', 'Qnt2Bln11', 'RpBln11', 'QntBln12', 'Qnt2Bln12', 'RpBln12'];
    protected $casts = ['Tahun' => 'integer', 'QntBln1' => 'float', 'Qnt2Bln1' => 'float', 'RpBln1' => 'float', 'QntBln2' => 'float', 'Qnt2Bln2' => 'float', 'RpBln2' => 'float', 'QntBln3' => 'float', 'Qnt2Bln3' => 'float', 'RpBln3' => 'float', 'QntBln4' => 'float', 'Qnt2Bln4' => 'float', 'RpBln4' => 'float', 'QntBln5' => 'float', 'Qnt2Bln5' => 'float', 'RpBln5' => 'float', 'QntBln6' => 'float', 'Qnt2Bln6' => 'float', 'RpBln6' => 'float', 'QntBln7' => 'float', 'Qnt2Bln7' => 'float', 'RpBln7' => 'float', 'QntBln8' => 'float', 'Qnt2Bln8' => 'float', 'RpBln8' => 'float', 'QntBln9' => 'float', 'Qnt2Bln9' => 'float', 'RpBln9' => 'float', 'QntBln10' => 'float', 'Qnt2Bln10' => 'float', 'RpBln10' => 'float', 'QntBln11' => 'float', 'Qnt2Bln11' => 'float', 'RpBln11' => 'float', 'QntBln12' => 'float', 'Qnt2Bln12' => 'float', 'RpBln12' => 'float'];
}
