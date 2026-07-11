<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DbBrowseConfigSeeder extends Seeder
{
    public function run(): void
    {
        $entries = $this->configMap();
        foreach ($entries as $row) {
            $exists = DB::table('dbbrowseconfigs')
                ->where('kodebrowse', $row['kodebrowse'])
                ->exists();
            if ($exists) {
                DB::table('dbbrowseconfigs')
                    ->where('kodebrowse', $row['kodebrowse'])
                    ->update($row);
            } else {
                $row['created_at'] = now();
                $row['updated_at'] = now();
                DB::table('dbbrowseconfigs')->insert($row);
            }
        }
        $this->command->info('Seeded ' . count($entries) . ' browse configs.');
    }

    private function configMap(): array
    {
        $alias1014 = [
            'cs_NamaCustSupp' => 'cs.NamaCustSupp',
            'cs_Alamat' => "RTRIM(LTRIM(ISNULL(cs.Alamat1,'') + CASE WHEN ISNULL(cs.Alamat2,'')='' THEN '' ELSE ' ' + cs.Alamat2 END))",
            'cs_Kota' => 'cs.Kota',
        ];
        $pf1014 = [['source_column' => 'Perkiraan', 'operator' => '=', 'type' => 'exact']];

        return [
            ['kodebrowse'=>'10051','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>json_encode(['Simbol','Tipe','DK']),'joins'=>json_encode(["LEFT JOIN DBAKSESPERKIRAAN ak ON ak.Perkiraan = DBPERKIRAAN.Perkiraan AND ak.UserMode = :userMode"]),'whereextra'=>"AND DBPERKIRAAN.Tipe = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1005','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>json_encode(['Simbol']),'joins'=>null,'whereextra'=>"AND DBPERKIRAAN.Tipe = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'100444','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND DBPERKIRAAN.Tipe = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10053','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND DBPERKIRAAN.Kelompok = 3 AND DBPERKIRAAN.Tipe = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10054','tablename'=>'DBLRHPP','keyfield'=>'Nomor','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10055','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>json_encode(["LEFT JOIN DBAKSESPERKIRAAN ak ON ak.Perkiraan = DBPERKIRAAN.Perkiraan AND ak.UserMode = :userMode"]),'whereextra'=>"AND DBPERKIRAAN.Tipe = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10059','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>json_encode(["LEFT JOIN DBAKSESPERKIRAAN ak ON ak.Perkiraan = DBPERKIRAAN.Perkiraan AND ak.UserMode = :userMode"]),'whereextra'=>"AND DBPERKIRAAN.Tipe = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'100409','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>json_encode(["INNER JOIN DBPOSTHUTPIUT pht ON pht.Perkiraan = DBPERKIRAAN.Perkiraan"]),'whereextra'=>"AND pht.Kode = 'HT'",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'100408','tablename'=>'DBPERKIRAAN','keyfield'=>'Perkiraan','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>json_encode(["INNER JOIN DBPOSTHUTPIUT pht ON pht.Perkiraan = DBPERKIRAAN.Perkiraan"]),'whereextra'=>"AND pht.Kode = 'PT'",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10141','tablename'=>'vwBrowsSupp','keyfield'=>'KodeCustSupp','labelfield'=>'NamaCustSupp','query'=>null,'additionalfields'=>json_encode(['Alamat','Telpon']),'joins'=>null,'whereextra'=>"WHERE IsAktif = 1 AND Jenis = 2",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10142','tablename'=>'vwBrowsCust','keyfield'=>'KodeCustSupp','labelfield'=>'NamaCustSupp','query'=>null,'additionalfields'=>json_encode(['Alamat','Kota','DueDate','JENIS','IsPpn']),'joins'=>null,'whereextra'=>"WHERE IsAktif = 1 AND Jenis = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10143','tablename'=>'vwBrowsExpedisi','keyfield'=>'KodeCustSupp','labelfield'=>'NamaCustSupp','query'=>null,'additionalfields'=>json_encode(['Alamat','Telpon']),'joins'=>null,'whereextra'=>"WHERE IsAktif = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1014','tablename'=>'vwGroupCustSupp','keyfield'=>'KodeCustSupp','labelfield'=>'cs_NamaCustSupp','query'=>null,'additionalfields'=>json_encode(['cs_Alamat','cs_Kota','Perkiraan']),'joins'=>json_encode(["LEFT JOIN DBCUSTSUPP cs ON cs.KodeCustSupp = vwGroupCustSupp.KodeCustSupp"]),'whereextra'=>null,'aliasfields'=>json_encode($alias1014),'parentfilters'=>json_encode($pf1014),'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'911','tablename'=>'DBBARANG','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>json_encode(['Isi2','Sat1','Sat2']),'joins'=>null,'whereextra'=>"AND KodeGrp = 'BJ'",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'912','tablename'=>'DBBARANG','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'913','tablename'=>'DBBARANG','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>json_encode(['Isi2','NFix','Kontrak']),'joins'=>json_encode(["LEFT JOIN DBARANGCUSTOMER bc ON bc.KodeBrg = DBBARANG.KodeBrg"]),'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'914','tablename'=>'DBLOKASI','keyfield'=>'Lokasi','labelfield'=>'Lokasi','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'915','tablename'=>'DBBARANG','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND IsAktif = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'917','tablename'=>'DBBARANG','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND IsAktif = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'120302','tablename'=>'vwBarang','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>json_encode(['Sat1','Sat2','Isi1','Isi2','NFix']),'joins'=>null,'whereextra'=>"AND (IsBarang = 1 OR IsBarang = 2) AND IsAktif = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'3001101','tablename'=>'DBBARANG','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>json_encode(['Sat1','Sat2','Isi']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'916','tablename'=>'DBGUDANG','keyfield'=>'KodeGdg','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'11002','tablename'=>'DBGUDANG','keyfield'=>'KodeGdg','labelfield'=>'Nama','query'=>null,'additionalfields'=>json_encode(['Alamat']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'11009','tablename'=>'DBGUDANG','keyfield'=>'KodeGdg','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1004','tablename'=>'DBDEVISI','keyfield'=>'Devisi','labelfield'=>'NamaDevisi','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'11011','tablename'=>'DBKOTA','keyfield'=>'KodeKota','labelfield'=>'NamaKota','query'=>null,'additionalfields'=>json_encode(['KodeArea','NamaArea']),'joins'=>json_encode(["LEFT JOIN DBAREA ar ON ar.KodeArea = DBKOTA.KodeArea"]),'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'110011','tablename'=>'DBSUBGROUP','keyfield'=>'KodeSubGrp','labelfield'=>'NamaSubGrp','query'=>null,'additionalfields'=>null,'joins'=>json_encode(["LEFT JOIN DBGROUP g ON g.KodeGrp = DBSUBGROUP.KodeGrp"]),'whereextra'=>"AND g.KodeGrp NOT IN ('BJ','BU')",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1100112','tablename'=>'DBGROUP','keyfield'=>'KodeGrp','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND KodeGrp NOT IN ('BJ','BU')",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'110012','tablename'=>'DBGROUP','keyfield'=>'KodeGrp','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'110013','tablename'=>'DBGROUP','keyfield'=>'KodeGrp','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'110014','tablename'=>'DBSUBGROUP','keyfield'=>'KodeSubGrp','labelfield'=>'NamaSubGrp','query'=>null,'additionalfields'=>json_encode(['KodeGrp','Nama']),'joins'=>json_encode(["LEFT JOIN DBGROUP g ON g.KodeGrp = DBSUBGROUP.KodeGrp"]),'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'157','tablename'=>'DBSUBGROUP','keyfield'=>'KodeSubGrp','labelfield'=>'NamaSubGrp','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1576','tablename'=>'DBKARYAWAN','keyfield'=>'KeyNIK','labelfield'=>'Nama','query'=>null,'additionalfields'=>json_encode(['NIK']),'joins'=>null,'whereextra'=>"AND IsAktif = 1 AND KodeBag NOT IN ('ASM')",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1577','tablename'=>'DBKARYAWAN','keyfield'=>'NIK','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>json_encode(["LEFT JOIN DBASM asm ON asm.KeyNIK = DBKARYAWAN.KeyNIK"]),'whereextra'=>"AND DBKARYAWAN.IsAktif = 1 AND asm.KeyNIK IS NULL",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'15779','tablename'=>'DBKARYAWAN','keyfield'=>'NIK','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND IsAktif = 1 AND KodeBag NOT IN ('ASM')",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'15780','tablename'=>'DBKARYAWAN','keyfield'=>'NIK','labelfield'=>'Nama','query'=>null,'additionalfields'=>json_encode(['Tarif']),'joins'=>json_encode(["LEFT JOIN DBTARIFTENAKER tt ON tt.KeyNIK = DBKARYAWAN.KeyNIK"]),'whereextra'=>"AND DBKARYAWAN.IsAktif = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'100413','tablename'=>'DBAKTIVA','keyfield'=>'NoMuka','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>json_encode(['KodeBag','Devisi']),'joins'=>json_encode(["LEFT JOIN DBBAGIAN bg ON bg.KodeBag = DBAKTIVA.KodeBag","LEFT JOIN DBPERKIRAAN pk ON pk.Perkiraan = DBAKTIVA.Perkiraan"]),'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'100412','tablename'=>'DBAKTIVA','keyfield'=>'NoMuka','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND Kelompok = 0",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'100405','tablename'=>'DBGIRO','keyfield'=>'NoGiro','labelfield'=>'Bank','query'=>null,'additionalfields'=>json_encode(['TglGiro','Jumlah','Valas','Kurs']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'100406','tablename'=>'DBGIRO','keyfield'=>'NoGiro','labelfield'=>'Bank','query'=>null,'additionalfields'=>json_encode(['TglGiro','Jumlah','Valas','Kurs']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1006','tablename'=>'DBVALAS','keyfield'=>'KodeVls','labelfield'=>'NamaVls','query'=>null,'additionalfields'=>json_encode(['Kurs']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'11001','tablename'=>'DBVALAS','keyfield'=>'KodeVls','labelfield'=>'NamaVls','query'=>null,'additionalfields'=>json_encode(['Kurs']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'2082','tablename'=>'DBVALAS','keyfield'=>'KodeVls','labelfield'=>'NamaVls','query'=>null,'additionalfields'=>json_encode(['Kurs']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1008','tablename'=>'DBKATEGORI','keyfield'=>'KodeKategori','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10081','tablename'=>'DBKATEGORIBRGJADI','keyfield'=>'KodeKategori','labelfield'=>'Keterangan','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1007','tablename'=>'DBARUSKAS','keyfield'=>'Kodeak','labelfield'=>'Namaak','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10071','tablename'=>'DBARUSKASDET','keyfield'=>'Kodesubak','labelfield'=>'Namasubak','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1002','tablename'=>'DBBAGIAN','keyfield'=>'KodeBag','labelfield'=>'Namabag','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'10021','tablename'=>'DBDEPART','keyfield'=>'KdDep','labelfield'=>'NmDep','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1003','tablename'=>'DBJABATAN','keyfield'=>'KodeJab','labelfield'=>'Namajab','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'251050','tablename'=>'DBTIPETRANS','keyfield'=>'KodeTipe','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'30056','tablename'=>'DBTIPETRANS','keyfield'=>'KodeTipe','labelfield'=>'Nama','query'=>null,'additionalfields'=>json_encode(['KodeSubTipe']),'joins'=>json_encode(["LEFT JOIN DBSUBTIPETRANS st ON st.KodeTipe = DBTIPETRANS.KodeTipe"]),'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'30057','tablename'=>'DBTIPETRANS','keyfield'=>'KodeTipe','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND IsJasaBeliJual = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'110015','tablename'=>'DBJNSTAMBAHAN','keyfield'=>'KodeJnsTambahan','labelfield'=>'Nama','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'110016','tablename'=>'DBBARANG','keyfield'=>'KodeBrg','labelfield'=>'NamaBrg','query'=>null,'additionalfields'=>null,'joins'=>null,'whereextra'=>"AND KodeGrp NOT IN ('BJ','BU') AND IsAktif = 1",'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'1250','tablename'=>'DBEXPEDISI','keyfield'=>'KodeExp','labelfield'=>'NamaExp','query'=>null,'additionalfields'=>json_encode(['Alamat1','Alamat2','Kota']),'joins'=>null,'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
            ['kodebrowse'=>'91117','tablename'=>'vwBrowsSPK','keyfield'=>'NOSPK','labelfield'=>'KodeBrg','query'=>null,'additionalfields'=>json_encode(['NamaBrg','NOSO','NamaCustSupp']),'joins'=>json_encode(["LEFT JOIN DBBARANG b ON b.KodeBrg = vwBrowsSPK.KodeBrg","LEFT JOIN DBSO c ON c.NOBUKTI = vwBrowsSPK.NOSO","LEFT JOIN DBCUSTSUPP d ON d.KodeCustSupp = c.KodeCustSupp"]),'whereextra'=>null,'aliasfields'=>null,'parentfilters'=>null,'params'=>null,'isactive'=>1],
        ];
    }
}