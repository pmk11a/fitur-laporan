<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinishingDET extends Model
{
    protected $table = 'DBFinishingDET';
    protected $fillable = ['NOBUKTI', 'URUT', 'KODEBRG', 'QNT', 'NOSAT', 'SATUAN', 'ISI', 'NoSPK', 'HPP', 'JenisKerja', 'Kertas', 'Waktu', 'KetDetail', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'I1', 'I2', 'I3', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'E1', 'E2', 'E3', 'N1', 'N2', 'CSI', 'L1', 'L2', 'L3', 'L4', 'L5', 'L6', 'L7', 'L8', 'L9', 'HasilBaik', 'HasilRusak', 'KertasReject', 'K1', 'P1', 'NIK', 'Jml1', 'Jml2', 'Jml3', 'Jml4', 'Jml5', 'Jml6', 'Jml7', 'Jml8', 'Jml9', 'Jml10', 'Jml11', 'Jml12', 'Jml13', 'Jml14', 'Jml15', 'Jml16', 'Jml17', 'Jml18', 'KetR1', 'KetR2', 'KetR3', 'KetR4', 'KetR5', 'KetR6', 'KetR7', 'KetR8', 'KetR9', 'KetR10', 'KetR11', 'KetR12', 'KetR13', 'KetR14', 'KetR15', 'KetR16', 'KetR17', 'KetR18', 'QntCetak', 'QntTambahan', 'QntSpesimen', 'JmlKR1', 'JmlKR2', 'KetKRL1', 'KetKRL2', 'KetKRL3', 'KetKR1', 'KetKR2', 'PR1', 'PR2', 'PR3', 'PR4', 'PR5', 'PR6', 'PR7', 'PR8', 'PR9', 'PR10', 'PR11', 'PR12', 'PR13', 'PR14', 'PR15', 'PR16', 'PR17', 'PR18'];
    protected $casts = ['URUT' => 'integer', 'QNT' => 'float', 'NOSAT' => 'integer', 'ISI' => 'float', 'HPP' => 'float', 'JenisKerja' => 'integer', 'Kertas' => 'float', 'Waktu' => 'float', 'C1' => 'float', 'C2' => 'float', 'C3' => 'float', 'C4' => 'float', 'C5' => 'float', 'C6' => 'float', 'I1' => 'float', 'I2' => 'float', 'I3' => 'float', 'H1' => 'float', 'H2' => 'float', 'H3' => 'float', 'H4' => 'float', 'H5' => 'float', 'H6' => 'float', 'E1' => 'float', 'E2' => 'float', 'E3' => 'float', 'N1' => 'float', 'N2' => 'float', 'CSI' => 'float', 'L1' => 'float', 'L2' => 'float', 'L3' => 'float', 'L4' => 'float', 'L5' => 'float', 'L6' => 'float', 'L7' => 'float', 'L8' => 'float', 'L9' => 'float', 'HasilBaik' => 'float', 'HasilRusak' => 'float', 'KertasReject' => 'float', 'K1' => 'float', 'P1' => 'float', 'Jml1' => 'float', 'Jml2' => 'float', 'Jml3' => 'float', 'Jml4' => 'float', 'Jml5' => 'float', 'Jml6' => 'float', 'Jml7' => 'float', 'Jml8' => 'float', 'Jml9' => 'float', 'Jml10' => 'float', 'Jml11' => 'float', 'Jml12' => 'float', 'Jml13' => 'float', 'Jml14' => 'float', 'Jml15' => 'float', 'Jml16' => 'float', 'Jml17' => 'float', 'Jml18' => 'float', 'QntCetak' => 'float', 'QntTambahan' => 'float', 'QntSpesimen' => 'float', 'JmlKR1' => 'float', 'JmlKR2' => 'float'];
}
