<?php

namespace App\Services;

use App\Models\PERKIRAAN;
use App\Models\BARANG;
use App\Models\CUSTSUPP;
use App\Models\GUDANG;
use App\Models\DEVISI;
use Illuminate\Support\Facades\DB;

/**
 * BrowseService — mirrors Delphi FrmBrows.pas FilterDataBrows case statement
 *
 * Maps KodeBrowse integer (from Delphi) → SQL query config.
 * Each config defines: table, keyField, labelField, additionalFields, whereConditions.
 *
 * SOLID:
 * - S: Single responsibility — browse query logic only
 * - O: Add new KodeBrowse → add entry in $configMap, no class changes
 * - D: Controller injects BrowseService, not hardcoded in controller
 */
class BrowseService
{
    /**
     * Config for each browse type.
     * Key = KodeBrowse string (integer from Delphi).
     * Value = array with table, keyField, labelField, additionalFields, joins, whereExtra.
     */
    private static function getConfigMap(): array
    {
        return [
            // ==================== PERKIRAAN ====================
            '10051' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => ['Simbol', 'Tipe', 'DK'],
                'joins' => [
                    'LEFT JOIN DBAKSESPERKIRAAN ak ON ak.Perkiraan = DBPERKIRAAN.Perkiraan AND ak.UserMode = :userMode',
                ],
                'whereExtra' => "AND DBPERKIRAAN.Tipe = 1",
            ],
            '1005' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => ['Simbol'],
                'whereExtra' => "AND DBPERKIRAAN.Tipe = 1",
            ],
            '100444' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'whereExtra' => "AND DBPERKIRAAN.Tipe = 1",
            ],
            '10053' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'whereExtra' => "AND DBPERKIRAAN.Kelompok = 3 AND DBPERKIRAAN.Tipe = 1",
            ],
            '10054' => [
                'table'  => 'DBLRHPP',
                'keyField' => 'Nomor',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '10055' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'joins' => ['LEFT JOIN DBAKSESPERKIRAAN ak ON ak.Perkiraan = DBPERKIRAAN.Perkiraan AND ak.UserMode = :userMode'],
                'whereExtra' => "AND DBPERKIRAAN.Tipe = 1",
            ],
            '10059' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'joins' => ['LEFT JOIN DBAKSESPERKIRAAN ak ON ak.Perkiraan = DBPERKIRAAN.Perkiraan AND ak.UserMode = :userMode'],
                'whereExtra' => "AND DBPERKIRAAN.Tipe = 1",
            ],

            // ==================== HUTANG / PIUTANG ACCOUNTS ====================
            // Hutang accounts (Kode='HT') - used by 20301, 20302, 20303, 20304, 20305
            '100409' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'joins' => ['INNER JOIN DBPOSTHUTPIUT pht ON pht.Perkiraan = DBPERKIRAAN.Perkiraan'],
                'whereExtra' => "AND pht.Kode = 'HT'",
            ],
            // Piutang accounts (Kode='PT') - used by 20401, 20402, 20403, 20404, 20405
            '100408' => [
                'table'  => 'DBPERKIRAAN',
                'keyField' => 'Perkiraan',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'joins' => ['INNER JOIN DBPOSTHUTPIUT pht ON pht.Perkiraan = DBPERKIRAAN.Perkiraan'],
                'whereExtra' => "AND pht.Kode = 'PT'",
            ],

            // ==================== GUDANG ====================
            '10141' => [
                'table'  => 'vwBrowsSupp',
                'keyField' => 'KodeCustSupp',
                'labelField' => 'NamaCustSupp',
                'additionalFields' => ['Alamat', 'Telpon'],
                'whereExtra' => "WHERE IsAktif = 1 AND Jenis = 2",
            ],
            '10142' => [
                'table'  => 'vwBrowsCust',
                'keyField' => 'KodeCustSupp',
                'labelField' => 'NamaCustSupp',
                'additionalFields' => ['Alamat', 'Kota', 'DueDate', 'JENIS', 'IsPpn'],
                'whereExtra' => "WHERE IsAktif = 1 AND Jenis = 1",
            ],
            '10143' => [
                'table'  => 'vwBrowsExpedisi',
                'keyField' => 'KodeCustSupp',
                'labelField' => 'NamaCustSupp',
                'additionalFields' => ['Alamat', 'Telpon'],
                'whereExtra' => "WHERE IsAktif = 1",
            ],
            '1014' => [
                'table'  => 'vwGroupCustSupp',
                'keyField' => 'KodeCustSupp',
                'labelField' => 'cs_NamaCustSupp',
                'additionalFields' => ['cs_Alamat', 'cs_Kota', 'Perkiraan'],
                'joins' => ['LEFT JOIN DBCUSTSUPP cs ON cs.KodeCustSupp = vwGroupCustSupp.KodeCustSupp'],
                'whereExtra' => null,
                'alias_fields' => [
                    'cs_NamaCustSupp' => 'cs.NamaCustSupp',
                    'cs_Alamat' => "RTRIM(LTRIM(ISNULL(cs.Alamat1,'') + CASE WHEN ISNULL(cs.Alamat2,'')='' THEN '' ELSE ' ' + cs.Alamat2 END))",
                    'cs_Kota' => 'cs.Kota',
                ],
                'parent_filters' => [
                    ['source_column' => 'Perkiraan', 'operator' => '=', 'type' => 'exact'],
                ],
            ],

            // ==================== BARANG ====================
            '911' => [
                'table'  => 'DBBARANG',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => ['Isi2', 'Sat1', 'Sat2'],
                'whereExtra' => "AND KodeGrp = 'BJ'",
            ],
            '912' => [
                'table'  => 'DBBARANG',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '913' => [
                'table'  => 'DBBARANG',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => ['Isi2', 'NFix', 'Kontrak'],
                'joins' => ['LEFT JOIN DBARANGCUSTOMER bc ON bc.KodeBrg = DBBARANG.KodeBrg'],
                'whereExtra' => null,
            ],
            '914' => [
                'table'  => 'DBLOKASI',
                'keyField' => 'Lokasi',
                'labelField' => 'Lokasi',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '915' => [
                'table'  => 'DBBARANG',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => [],
                'whereExtra' => "AND IsAktif = 1",
            ],
            '917' => [
                'table'  => 'DBBARANG',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => [],
                'whereExtra' => "AND IsAktif = 1",
            ],
            '120302' => [
                'table'  => 'vwBarang',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => ['Sat1', 'Sat2', 'Isi1', 'Isi2', 'NFix'],
                'whereExtra' => "AND (IsBarang = 1 OR IsBarang = 2) AND IsAktif = 1",
            ],
            '3001101' => [
                'table'  => 'DBBARANG',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => ['Sat1', 'Sat2', 'Isi'],
                'whereExtra' => null,
            ],

            // ==================== GUDANG ====================
            '916' => [
                'table'  => 'DBGUDANG',
                'keyField' => 'KodeGdg',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '11002' => [
                'table'  => 'DBGUDANG',
                'keyField' => 'KodeGdg',
                'labelField' => 'Nama',
                'additionalFields' => ['Alamat'],
                'whereExtra' => null,
            ],
            '11009' => [
                'table'  => 'DBGUDANG',
                'keyField' => 'KodeGdg',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => null,
            ],

            // ==================== DEVISI ====================
            '1004' => [
                'table'  => 'DBDEVISI',
                'keyField' => 'Devisi',
                'labelField' => 'NamaDevisi',
                'additionalFields' => [],
                'whereExtra' => null,
            ],

            // ==================== KOTA ====================
            '11011' => [
                'table'  => 'DBKOTA',
                'keyField' => 'KodeKota',
                'labelField' => 'NamaKota',
                'additionalFields' => ['KodeArea', 'NamaArea'],
                'joins' => ['LEFT JOIN DBAREA ar ON ar.KodeArea = DBKOTA.KodeArea'],
                'whereExtra' => null,
            ],

            // ==================== GRUP / SUB GRUP ====================
            '110011' => [
                'table'  => 'DBSUBGROUP',
                'keyField' => 'KodeSubGrp',
                'labelField' => 'NamaSubGrp',
                'additionalFields' => [],
                'joins' => ['LEFT JOIN DBGROUP g ON g.KodeGrp = DBSUBGROUP.KodeGrp'],
                'whereExtra' => "AND g.KodeGrp NOT IN ('BJ','BU')",
            ],
            '1100112' => [
                'table'  => 'DBGROUP',
                'keyField' => 'KodeGrp',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => "AND KodeGrp NOT IN ('BJ','BU')",
            ],
            '110012' => [
                'table'  => 'DBGROUP',
                'keyField' => 'KodeGrp',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '110013' => [
                'table'  => 'DBGROUP',
                'keyField' => 'KodeGrp',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '110014' => [
                'table'  => 'DBSUBGROUP',
                'keyField' => 'KodeSubGrp',
                'labelField' => 'NamaSubGrp',
                'additionalFields' => ['KodeGrp', 'Nama'],
                'joins' => ['LEFT JOIN DBGROUP g ON g.KodeGrp = DBSUBGROUP.KodeGrp'],
                'whereExtra' => null,
            ],
            '157' => [
                'table'  => 'DBSUBGROUP',
                'keyField' => 'KodeSubGrp',
                'labelField' => 'NamaSubGrp',
                'additionalFields' => [],
                'whereExtra' => null,
            ],

            // ==================== KARYAWAN / SALES ====================
            '1576' => [
                'table'  => 'DBKARYAWAN',
                'keyField' => 'KeyNIK',
                'labelField' => 'Nama',
                'additionalFields' => ['NIK'],
                'whereExtra' => "AND IsAktif = 1 AND KodeBag NOT IN ('ASM')",
            ],
            '1577' => [
                'table'  => 'DBKARYAWAN',
                'keyField' => 'NIK',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'joins' => ['LEFT JOIN DBASM asm ON asm.KeyNIK = DBKARYAWAN.KeyNIK'],
                'whereExtra' => "AND DBKARYAWAN.IsAktif = 1 AND asm.KeyNIK IS NULL",
            ],
            '15779' => [
                'table'  => 'DBKARYAWAN',
                'keyField' => 'NIK',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => "AND IsAktif = 1 AND KodeBag NOT IN ('ASM')",
            ],
            '15780' => [
                'table'  => 'DBKARYAWAN',
                'keyField' => 'NIK',
                'labelField' => 'Nama',
                'additionalFields' => ['Tarif'],
                'joins' => ['LEFT JOIN DBTARIFTENAKER tt ON tt.KeyNIK = DBKARYAWAN.KeyNIK'],
                'whereExtra' => "AND DBKARYAWAN.IsAktif = 1",
            ],

            // ==================== AKTIVA ====================
            '100413' => [
                'table'  => 'DBAKTIVA',
                'keyField' => 'NoMuka',
                'labelField' => 'Keterangan',
                'additionalFields' => ['KodeBag', 'Devisi'],
                'joins' => ['LEFT JOIN DBBAGIAN bg ON bg.KodeBag = DBAKTIVA.KodeBag',
                            'LEFT JOIN DBPERKIRAAN pk ON pk.Perkiraan = DBAKTIVA.Perkiraan'],
                'whereExtra' => null,
            ],
            '100412' => [
                'table'  => 'DBAKTIVA',
                'keyField' => 'NoMuka',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'whereExtra' => "AND Kelompok = 0",
            ],

            // ==================== GIRO ====================
            '100405' => [
                'table'  => 'DBGIRO',
                'keyField' => 'NoGiro',
                'labelField' => 'Bank',
                'additionalFields' => ['TglGiro', 'Jumlah', 'Valas', 'Kurs'],
                'whereExtra' => null,
            ],
            '100406' => [
                'table'  => 'DBGIRO',
                'keyField' => 'NoGiro',
                'labelField' => 'Bank',
                'additionalFields' => ['TglGiro', 'Jumlah', 'Valas', 'Kurs'],
                'whereExtra' => null,
            ],

            // ==================== VALAS ====================
            '1006' => [
                'table'  => 'DBVALAS',
                'keyField' => 'KodeVls',
                'labelField' => 'NamaVls',
                'additionalFields' => ['Kurs'],
                'whereExtra' => null,
            ],
            '11001' => [
                'table'  => 'DBVALAS',
                'keyField' => 'KodeVls',
                'labelField' => 'NamaVls',
                'additionalFields' => ['Kurs'],
                'whereExtra' => null,
            ],
            '2082' => [
                'table'  => 'DBVALAS',
                'keyField' => 'KodeVls',
                'labelField' => 'NamaVls',
                'additionalFields' => ['Kurs'],
                'whereExtra' => null,
            ],

            // ==================== KATEGORI ====================
            '1008' => [
                'table'  => 'DBKATEGORI',
                'keyField' => 'KodeKategori',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '10081' => [
                'table'  => 'DBKATEGORIBRGJADI',
                'keyField' => 'KodeKategori',
                'labelField' => 'Keterangan',
                'additionalFields' => [],
                'whereExtra' => null,
            ],

            // ==================== ARUS KAS ====================
            '1007' => [
                'table'  => 'DBARUSKAS',
                'keyField' => 'Kodeak',
                'labelField' => 'Namaak',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '10071' => [
                'table'  => 'DBARUSKASDET',
                'keyField' => 'Kodesubak',
                'labelField' => 'Namasubak',
                'additionalFields' => [],
                'whereExtra' => null,
            ],

            // ==================== BAGIAN / DEPARTEMEN ====================
            '1002' => [
                'table'  => 'DBBAGIAN',
                'keyField' => 'KodeBag',
                'labelField' => 'Namabag',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '10021' => [
                'table'  => 'DBDEPART',
                'keyField' => 'KdDep',
                'labelField' => 'NmDep',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '1003' => [
                'table'  => 'DBJABATAN',
                'keyField' => 'KodeJab',
                'labelField' => 'Namajab',
                'additionalFields' => [],
                'whereExtra' => null,
            ],

            // ==================== TIPE TRANSAKSI ====================
            '251050' => [
                'table'  => 'DBTIPETRANS',
                'keyField' => 'KodeTipe',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => null,
            ],
            '30056' => [
                'table'  => 'DBTIPETRANS',
                'keyField' => 'KodeTipe',
                'labelField' => 'Nama',
                'additionalFields' => ['KodeSubTipe'],
                'joins' => ['LEFT JOIN DBSUBTIPETRANS st ON st.KodeTipe = DBTIPETRANS.KodeTipe'],
                'whereExtra' => null,
            ],
            '30057' => [
                'table'  => 'DBTIPETRANS',
                'keyField' => 'KodeTipe',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => "AND IsJasaBeliJual = 1",
            ],

            // ==================== JENIS TAMBAHAN ====================
            '110015' => [
                'table'  => 'DBJNSTAMBAHAN',
                'keyField' => 'KodeJnsTambahan',
                'labelField' => 'Nama',
                'additionalFields' => [],
                'whereExtra' => null,
            ],

            // ==================== BAHAN ====================
            '110016' => [
                'table'  => 'DBBARANG',
                'keyField' => 'KodeBrg',
                'labelField' => 'NamaBrg',
                'additionalFields' => [],
                'whereExtra' => "AND KodeGrp NOT IN ('BJ','BU') AND IsAktif = 1",
            ],

            // ==================== EKSPRESI ====================
            '1250' => [
                'table'  => 'DBEXPEDISI',
                'keyField' => 'KodeExp',
                'labelField' => 'NamaExp',
                'additionalFields' => ['Alamat1', 'Alamat2', 'Kota'],
                'whereExtra' => null,
            ],

            // ==================== SPK / SO ====================
            '91117' => [
                'table'  => 'vwBrowsSPK',
                'keyField' => 'NOSPK',
                'labelField' => 'KodeBrg',
                'additionalFields' => ['NamaBrg', 'NOSO', 'NamaCustSupp'],
                'joins' => [
                    'LEFT JOIN DBBARANG b ON b.KodeBrg = vwBrowsSPK.KodeBrg',
                    'LEFT JOIN DBSO c ON c.NOBUKTI = vwBrowsSPK.NOSO',
                    'LEFT JOIN DBCUSTSUPP d ON d.KodeCustSupp = c.KodeCustSupp',
                ],
                'whereExtra' => null,
            ],
        ];
    }

    /**
     * Get browse config for a KodeBrowse.
     */
    public function getConfig(string $kodeBrowse): ?array
    {
        $map = self::getConfigMap();
        return $map[$kodeBrowse] ?? null;
    }

    /**
     * List all available browse types.
     */
    public function types(): array
    {
        $map = self::getConfigMap();
        return array_map(function ($kode, $config) {
            return [
                'kodeBrowse' => $kode,
                'keyField' => $config['keyField'],
                'labelField' => $config['labelField'],
                'additionalFields' => $config['additionalFields'] ?? [],
            ];
        }, array_keys($map), array_values($map));
    }

    /**
     * Search records for a browse type.
     *
     * @param string $kodeBrowse
     * @param string $q  Search query
     * @param int $limit
     * @param string|null $userMode  User mode for access filter
     * @return array
     */
    public function search(string $kodeBrowse, string $q = '', int $limit = 20, ?string $userMode = null, array $parentFilters = []): array
    {
        $config = $this->getConfig($kodeBrowse);
        if (!$config) {
            return [];
        }

        $keyField = $config['keyField'];
        $labelField = $config['labelField'];
        $table = $config['table'];
        $additionalFields = $config['additionalFields'] ?? [];
        $joins = $config['joins'] ?? [];
        $whereExtra = $config['whereExtra'] ?? '';
        $aliasFields = $config['alias_fields'] ?? [];
        $bindings = [];

        // Build SELECT — use alias_fields for prefixed fields (e.g. cs_NamaCustSupp → cs.NamaCustSupp)
        $selectFields = array_merge([$keyField, $labelField], $additionalFields);
        $selectList = implode(', ', array_map(function ($f) use ($table, $aliasFields) {
            if (isset($aliasFields[$f])) {
                return $aliasFields[$f] . " AS {$f}";
            }
            return "{$table}.{$f}";
        }, $selectFields));

        // Build base query
        $sql = "SELECT TOP {$limit} {$selectList} FROM {$table}";

        // Add joins
        if (!empty($joins)) {
            $sql .= ' ' . implode(' ', $joins);
        }

        // Build WHERE
        if ($q !== '') {
            // Resolve label column (may be aliased from another table)
            $labelCol = isset($aliasFields[$labelField])
                ? $aliasFields[$labelField]
                : "{$table}.{$labelField}";
            $whereClause = "({$table}.{$keyField} LIKE :q1 OR {$labelCol} LIKE :q2)";
        } else {
            $whereClause = '';
        }

        if ($whereExtra) {
            // Strip any leading WHERE/AND/OR and normalize
            $extra = preg_replace('/^\s*(WHERE|AND|OR)\s+/i', '', $whereExtra);
            if (!empty($extra)) {
                if (!empty($whereClause)) {
                    $whereClause .= ' AND ' . $extra;
                } else {
                    $whereClause = $extra;
                }
            }
        }

        // Inject parent_filters if defined in config and values provided
        $pfConfig = $config['parent_filters'] ?? [];
        if (!empty($pfConfig) && !empty($parentFilters)) {
            foreach ($pfConfig as $pfIdx => $pf) {
                $fieldName = $pf['source_column'];
                if (!isset($parentFilters[$fieldName])) {
                    continue;
                }
                $op = $pf['operator'] ?? '=';
                $qualifiedCol = "{$table}.{$fieldName}";
                $bindingKey = "parent{$pfIdx}";
                $whereClause .= empty($whereClause) ? "" : " AND ";
                $whereClause .= "{$qualifiedCol} {$op} :{$bindingKey}";
                $bindings[$bindingKey] = $parentFilters[$fieldName];
            }
        }

        if (!empty(trim($whereClause))) {
            $sql .= ' WHERE ' . $whereClause;
        }

        // Order by key field
        $sql .= " ORDER BY {$table}.{$keyField}";

        if ($q !== '') {
            $bindings['q1'] = "%{$q}%";
            $bindings['q2'] = "%{$q}%";
        }
        if ($userMode && strpos(implode(' ', $joins), 'UserMode') !== false) {
            $bindings['userMode'] = $userMode;
        }

        try {
            // Set encoding to SYSTEM (Windows-1252) before query to get clean bytes from SQL Server
            $pdo = DB::connection('sqlsrv')->getPdo();
            $pdo->setAttribute(\PDO::SQLSRV_ATTR_ENCODING, \PDO::SQLSRV_ENCODING_SYSTEM);

            $results = DB::select($sql, $bindings);
            // Convert stdClass to array and filter invalid UTF-8 bytes
            return array_map(function ($r) {
                $row = (array) $r;
                foreach ($row as $k => $v) {
                    if (is_string($v)) {
                        // Convert Windows-1252 to UTF-8 for proper JSON encoding
                        $row[$k] = mb_convert_encoding($v, 'UTF-8', 'Windows-1252');
                    }
                }
                return $row;
            }, $results);
        } catch (\Exception $e) {
            // Fallback: try using Eloquent model if available
            return $this->searchViaModel($kodeBrowse, $q, $limit);
        }
    }

    /**
     * Validate a single code and return full row data.
     */
    public function validateCode(string $kodeBrowse, string $code): ?array
    {
        $config = $this->getConfig($kodeBrowse);
        if (!$config) {
            return null;
        }

        $keyField = $config['keyField'];
        $table = $config['table'];
        $whereExtra = $config['whereExtra'] ?? '';

        $sql = "SELECT TOP 1 * FROM {$table} WHERE {$keyField} = :code";
        if ($whereExtra) {
            // Strip any leading WHERE/AND/OR and normalize
            $extra = preg_replace('/^\s*(WHERE|AND|OR)\s+/i', '', $whereExtra);
            if (!empty(trim($extra))) {
                $sql .= ' AND ' . trim($extra);
            }
        }

        try {
            $result = DB::select($sql, ['code' => $code]);
            $row = !empty($result) ? (array) $result[0] : null;
            if ($row) {
                // Ensure UTF-8 encoding for all string values
                foreach ($row as $k => $v) {
                    if (is_string($v)) {
                        $converted = @iconv('Windows-1252', 'UTF-8//IGNORE', $v);
                        $row[$k] = $converted !== false ? $converted : $v;
                    }
                }
            }
            return $row;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate multiple codes (batch) — used by tags mode.
     * Returns array of found records matching the codes.
     */
    public function validateBatch(string $kodeBrowse, array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        $config = $this->getConfig($kodeBrowse);
        if (!$config) {
            return [];
        }

        $keyField = $config['keyField'];
        $table = $config['table'];

        $placeholders = implode(',', array_fill(0, count($codes), '?'));
        $sql = "SELECT * FROM {$table} WHERE {$keyField} IN ({$placeholders})";

        try {
            $results = DB::select($sql, $codes);
            return array_map(fn($r) => (array) $r, $results);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all records for a browse type (no search filter).
     * Used by checkbox mode to load all items for selection.
     */
    public function getAll(string $kodeBrowse, int $limit = 500, ?string $userMode = null): array
    {
        return $this->search($kodeBrowse, '', $limit, $userMode);
    }

    /**
     * Fallback search using Eloquent model if raw SQL fails.
     */
    private function searchViaModel(string $kodeBrowse, string $q, int $limit): array
    {
        $modelMap = [
            '10051' => PERKIRAAN::class,
            '1005' => PERKIRAAN::class,
            '100444' => PERKIRAAN::class,
            '120302' => BARANG::class,
            '915' => BARANG::class,
            '917' => BARANG::class,
        ];

        $modelClass = $modelMap[$kodeBrowse] ?? null;
        if (!$modelClass) {
            return [];
        }

        $instance = new $modelClass();
        $table = $instance->getTable();

        $config = $this->getConfig($kodeBrowse);
        $keyField = $config['keyField'];
        $labelField = $config['labelField'];

        $query = $instance->newQuery();
        if ($q !== '') {
            $query->where(function ($inner) use ($q, $keyField, $labelField) {
                $inner->where($keyField, 'like', "%{$q}%")
                      ->orWhere($labelField, 'like', "%{$q}%");
            });
        }

        $query->limit($limit)->orderBy($keyField);
        return $query->get()->toArray();
    }
}