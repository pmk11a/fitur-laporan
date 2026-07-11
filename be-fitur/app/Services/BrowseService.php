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
     *
     * NOTE: This is the canonical source for kode_browse configs used by the
     * legacy Delphi reports. New reports can also register entries here or
     * define their own config via the GenericBrowseService.
     */
    public static function getConfigMap(): array
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
     *
     * Priority:
     * 1. Database-driven (GenericBrowseService) — always checked first
     * 2. Hardcoded map (getConfigMap) — fallback for legacy Delphi configs
     *
     * AdditionalFields merge logic:
     * - If the DB config is brand new (no hardcoded fallback exists), return it as-is.
     * - If a hardcoded fallback exists, merge hardcoded additionalFields into the DB
     *   config so that any fields defined in the canonical hardcoded list but missing
     *   from the DB row are automatically available.
     */
    public function getConfig(string $kodeBrowse): ?array
    {
        $map = self::getConfigMap();
        // 1st priority: database-driven (allows admin to override hardcoded configs)
        $dbConfig = app(GenericBrowseService::class)->find($kodeBrowse);
        if ($dbConfig) {
            // Merge additionalFields from hardcoded map for backward compatibility
            $hardcoded = $map[$kodeBrowse] ?? null;
            if ($hardcoded && !empty($hardcoded['additionalFields'])) {
                $existingAdditionalFields = $dbConfig['additionalFields'] ?? [];
                $merged = array_values(array_unique(array_merge(
                    $existingAdditionalFields,
                    $hardcoded['additionalFields']
                )));
                $dbConfig['additionalFields'] = $merged;
            }
            return $dbConfig;
        }

        // 2nd priority: code-driven map (canonical Delphi-mirrored entries)
        if (isset($map[$kodeBrowse])) {
            return $map[$kodeBrowse];
        }

        return null;
    }

    /**
     * List all available browse types.
     *
     * Priority: database configs override hardcoded ones with same kodeBrowse.
     */
    public function types(): array
    {
        $dbTypes = app(GenericBrowseService::class)->all();
        $dbMap = [];
        foreach ($dbTypes as $t) {
            $dbMap[$t['kodeBrowse']] = $t;
        }

        $map = self::getConfigMap();

        // Merge: hardcoded as base, database overrides
        $merged = $map;
        foreach ($dbMap as $k => $v) {
            $merged[$k] = $v; // DB always wins
        }

        // Build result with source info
        $result = [];
        foreach ($merged as $kode => $config) {
            $result[] = [
                'kodeBrowse' => $kode,
                'keyField' => $config['keyField'] ?? 'Kode',
                'labelField' => $config['labelField'] ?? 'Nama',
                'additionalFields' => $config['additionalFields'] ?? [],
                'source' => isset($dbMap[$kode]) ? 'database' : 'hardcoded',
            ];
        }

        return $result;
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
    /**
     * Detect if a query string contains corrupted Delphi-style single-quote tokens
     * (e.g., '%''%', ''''', ''''''') that indicate old filter substitution
     * placeholders rather than valid SQL. When detected and table-based fallback
     * is available, the caller should skip query-based execution.
     */
    private function looksLikeDelphiCorruptedQuery(string $sql): bool
    {
        // Common Delphi-to-SQL migration artifacts:
        // - '%''%'          → original: '%'+EditFilter.Text+'%'
        // - ''''            → original: single quote separator
        // - '+EditFilter+'  → original: Delphi variable
        // - '+EditFilter.Text+' → Delphi variable reference
        $patterns = [
            "%''%",          // search pattern artifact
            "'+'",           // concatenation artifact
            "%'+%",          // mixed artifact
            "EditFilter",    // raw Delphi variable name
            "\+'",           // concatenation with quote
        ];

        foreach ($patterns as $pattern) {
            if (stripos($sql, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    public function search(string $kodeBrowse, string $q = '', int $limit = 20, ?string $userMode = null, array $parentFilters = []): array
    {
        $config = $this->getConfig($kodeBrowse);
        if (!$config) {
            return [];
        }

        // ============================================================================
        // FIX: Merge hardcoded config's joins/whereExtra/parent_filters as fallback.
        // Some DB browse configs in `dbbrowseconfigs` were migrated from the hardcoded
        // config map but lost fields like `joins`, `whereExtra`, or `parent_filters`
        // during migration. When falling back to table-based execution (either
        // because there's no query, or because the query is Delphi-corrupted), we
        // merge missing fields from the hardcoded map so the table-based builder
        // still has the joins/where it needs.
        // ============================================================================
        $hardcoded = $this->getConfigMap()[$kodeBrowse] ?? null;
        if ($hardcoded) {
            // Merge `joins` if DB config has it empty/missing
            if (empty($config['joins']) && !empty($hardcoded['joins'])) {
                $config['joins'] = $hardcoded['joins'];
            }
            // Merge `whereExtra` if missing
            if (empty($config['whereExtra']) && !empty($hardcoded['whereExtra'])) {
                $config['whereExtra'] = $hardcoded['whereExtra'];
            }
            // Merge `parent_filters` if missing
            if (empty($config['parent_filters']) && !empty($hardcoded['parent_filters'])) {
                $config['parent_filters'] = $hardcoded['parent_filters'];
            }
            // Merge `additionalFields` if missing
            if (empty($config['additionalFields']) && !empty($hardcoded['additionalFields'])) {
                $config['additionalFields'] = $hardcoded['additionalFields'];
            }
            // Merge `alias_fields` if missing
            if (empty($config['alias_fields']) && !empty($hardcoded['alias_fields'])) {
                $config['alias_fields'] = $hardcoded['alias_fields'];
            }
        }

        $keyField = $config['keyField'];
        $labelField = $config['labelField'];
        $table = $config['table'] ?? null;
        $query = $config['query'] ?? null;
        $additionalFields = $config['additionalFields'] ?? [];
        $joins = $config['joins'] ?? [];
        $whereExtra = $config['whereExtra'] ?? '';
        $aliasFields = $config['alias_fields'] ?? [];
        $bindings = [];

        // ============================================================
        // MODE: QUERY-BASED (query field provided, overrides table-based)
        // If a query is defined, it ALWAYS takes precedence over table-based
        // construction. The table field may also be set for reference, but
        // the stored SQL should be used verbatim.
        // ============================================================
        if ($query !== null) {
            // Check if the query is a clean, substitutable SQL (no Delphi corruption).
            // Corrupted queries from old Delphi migration contain stray single-quote
            // patterns like '%''%' or '''' that won't match known substitution tokens.
            // In those cases, fall back to table-based if the config supports it.
            $isCorruptedQuery = $this->looksLikeDelphiCorruptedQuery($query);
            if (!$isCorruptedQuery || ($joins === null && $table === null)) {
                return $this->searchQueryBased($query, $keyField, $labelField, $aliasFields, $q, $limit, $bindings, $parentFilters, $config);
            }
            // Corrupted query + no table-based fallback available — still try query
        }

        // ============================================================
        // MODE: TABLE-BASED (fallback when no query is defined)
        // Build SELECT — use alias_fields for prefixed fields (e.g. cs_NamaCustSupp → cs.NamaCustSupp)
        // ============================================================
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
            // Normalize: collapse internal spaces so "w h e r e" → "where"
            // before stripping the leading keyword. This handles malformed config values
            // where spaces were accidentally inserted between letters.
            $normalizedExtra = preg_replace('/(?<=[A-Za-z])\s+(?=[A-Za-z])/', '', $whereExtra);
            // Strip leading WHERE/AND/OR (with optional surrounding whitespace)
            $extra = preg_replace('/^\s*(WHERE|AND|OR)\s+/i', '', $normalizedExtra);
            // Also strip any remaining leading AND/OR (handles "WHERE AND" edge case)
            $extra = ltrim($extra);
            if (preg_match('/^(AND|OR)\s+/i', $extra)) {
                $extra = preg_replace('/^(AND|OR)\s+/i', '', $extra);
            }
            $extra = trim($extra);
            if (!empty($extra)) {
                if (!empty(trim($whereClause))) {
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

        // Order by key field (table-based only)
        if ($table) {
            $sql .= " ORDER BY {$table}.{$keyField}";
        }

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
     * Search using a raw query (query-based browse config).
     *
     * This handles browse configs that use a custom SQL string instead of
     * table-based queries. Supports:
     * - Raw SQL strings (tablename = NULL, query = full SQL)
     * - WHERE clause injection via :q placeholder or EditFilter.Text substitution
     * - Stored Procedure calls via EXEC
     *
     * @param string $query       Full SQL query or SP call
     * @param string $keyField   Field to use as key
     * @param string $labelField Field to use as label
     * @param array $aliasFields  Map of aliased field names
     * @param string $q          Search query
     * @param int $limit          Row limit
     * @param array $bindings    Parameter bindings
     * @return array
     */
    private function searchQueryBased(string $query, string $keyField, string $labelField, array $aliasFields, string $q, int $limit, array $bindings, array $parentFilters = [], array $config = []): array
    {
        $sql = $query;

        // Inject parent_filters as named parameters for query-based configs.
        // Strategy: find occurrences of ''<P:fieldName>'' placeholder in the query
        // and replace them with ':bindKey' (without quotes, so the driver handles escaping).
        // Example:  A.NOBUKTI = ''<P:NoKira1>''  becomes  A.NOBUKTI = :qparent0
        // The parent filter value is bound securely through PDO.
        $pfConfig = $config['parent_filters'] ?? [];
        if (!empty($pfConfig) && !empty($parentFilters)) {
            foreach ($pfConfig as $pfIdx => $pf) {
                $sourceKey = $pf['source_column'] ?? '';
                if (empty($sourceKey) || !isset($parentFilters[$sourceKey])) continue;
                $op = $pf['operator'] ?? '=';
                $bindingKey = "qparent{$pfIdx}";
                $val = $parentFilters[$sourceKey];

                // Try pattern: ''<P:fieldname>'' (Delphi-style single quotes around placeholder)
                $placeholderInQuote = "''<P:{$sourceKey}>''";
                $placeholderPlain = "<P:{$sourceKey}>";

                if (strpos($sql, $placeholderInQuote) !== false) {
                    // Replace ''<P:field>'' with :bindKey (driver handles quoting)
                    $sql = str_replace($placeholderInQuote, ':' . $bindingKey, $sql);
                } elseif (strpos($sql, $placeholderPlain) !== false) {
                    // Replace standalone <P:field> with :bindKey
                    $sql = str_replace($placeholderPlain, ':' . $bindingKey, $sql);
                } else {
                    // Fallback: append AND condition to the WHERE clause
                    $col = $pf['column'] ?? "[" . $sourceKey . "]";
                    $sql .= ' AND ' . $col . ' ' . $op . ' :' . $bindingKey;
                }

                // Bind the value — PDO handles proper escaping
                $bindings[$bindingKey] = $val;
            }
        }

        // If the query already has WHERE/LIKE filter for EditFilter.Text,
        // we need to substitute it. Detect pattern: like ''%''+EditFilter.Text+''%''
        if ($q !== '') {
            // Replace Delphi-style filter: like ''%''+EditFilter.Text+''%''
            $escapedQ = str_replace("'", "''", $q);
            $sql = preg_replace(
                "/like\s*''%'\s*\+[\w.]+\.\w+\s*\+\s*'%''/i",
                "LIKE '$escapedQ%'",
                $sql
            );
            // Also replace simple EditFilter.Text patterns like: like ''%param%''
            $sql = preg_replace(
                "/LIKE\s*''%([^']+)%''/i",
                "LIKE '$escapedQ%'",
                $sql
            );
        }

        // Wrap as SELECT TOP if not already wrapped
        $selectStart = strtoupper(substr(ltrim($sql), 0, 6));
        if ($selectStart === 'SELECT') {
            // Insert TOP after SELECT if not present
            if (!preg_match('/^\s*SELECT\s+TOP\s+\d+/i', ltrim($sql))) {
                $sql = preg_replace('/^\s*SELECT\s+/i', 'SELECT TOP ' . $limit . ' ', $sql, 1);
            }
        }

        try {
            $pdo = DB::connection('sqlsrv')->getPdo();
            $pdo->setAttribute(\PDO::SQLSRV_ATTR_ENCODING, \PDO::SQLSRV_ENCODING_SYSTEM);

            $results = DB::select($sql, $bindings);

            // Filter results by search query in-memory (for query-based configs)
            // because WHERE injection is complex in raw SQL
            if ($q !== '') {
                $results = array_filter($results, function ($r) use ($keyField, $labelField, $q) {
                    $row = (array) $r;
                    $keyVal = strtolower($row[$keyField] ?? '');
                    $labelVal = strtolower($row[$labelField] ?? '');
                    $search = strtolower($q);
                    return stripos($keyVal, $search) !== false || stripos($labelVal, $search) !== false;
                });
                $results = array_slice(array_values($results), 0, $limit);
            }

            return array_map(function ($r) {
                $row = (array) $r;
                foreach ($row as $k => $v) {
                    if (is_string($v)) {
                        $row[$k] = mb_convert_encoding($v, 'UTF-8', 'Windows-1252');
                    }
                }
                return $row;
            }, $results);
        } catch (\Exception $e) {
            return [];
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
            // Normalize: collapse internal spaces so "w h e r e" → "where"
            $normalizedExtra = preg_replace('/(?<=[A-Za-z])\s+(?=[A-Za-z])/', '', $whereExtra);
            $extra = preg_replace('/^\s*(WHERE|AND|OR)\s+/i', '', $normalizedExtra);
            $extra = ltrim($extra);
            if (preg_match('/^(AND|OR)\s+/i', $extra)) {
                $extra = preg_replace('/^(AND|OR)\s+/i', '', $extra);
            }
            $extra = trim($extra);
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