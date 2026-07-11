<?php

namespace App\Services;

use App\Models\LabelGrup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportService
{
    public function __construct(
        protected BrowseService $browseService
    ) {
    }
    /**
     * Get menu items for sidebar based on user access
     */
    public function getMenuForUser(string $userId): array
    {
        $level1 = $this->getMenuLevel($userId, 1);
        $level2 = $this->getMenuLevel($userId, 2);
        $level3 = $this->getMenuLevel($userId, 3);
        $level4 = $this->getMenuLevel($userId, 4);
        $level5 = $this->getMenuLevel($userId, 5);

        return [
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
            'level4' => $level4,
            'level5' => $level5
        ];
    }

    private function getMenuLevel(string $userId, int $level, ?string $parentKode = null): array
    {
        $sql = "
            SELECT a.USERID, b.L0, a.L1, b.Keterangan as NmReport,
                   b.ACCESS as KodeReport, a.Access
            FROM DBFLMENUREPORT a
            LEFT OUTER JOIN DBMENUREPORT b ON b.KODEMENU = a.L1
            WHERE a.USERID = :userId AND a.Access = 1 AND b.L0 = :level";

        $params = ['userId' => $userId, 'level' => $level];

        if ($parentKode !== null) {
            $sql .= " AND a.L1 LIKE :parentPattern";
            $params['parentPattern'] = $parentKode . '%';
        }

        $sql .= " ORDER BY a.L1";
        $results = DB::connection('sqlsrv')->select($sql, $params);

        return array_map(fn($row) => [
            'USERID' => $row->USERID,
            'L0' => $row->L0,
            'L1' => $row->L1,
            'NmReport' => $row->NmReport,
            'KodeReport' => $row->KodeReport,
            'Access' => $row->Access
        ], $results);
    }

    /**
     * Get sidebar menu tree (hierarchical)
     * Hierarchy based on L0 level - items with same L0 are siblings
     */
    public function getSidebarMenu(string $userId): array
    {
        $allMenus = [];
        for ($level = 1; $level <= 7; $level++) {
            foreach ($this->getMenuLevel($userId, $level) as $menu) {
                $allMenus[$menu['L1']] = $menu;
            }
        }

        // Group by L0 level
        $byLevel = [];
        foreach ($allMenus as $kode => $menu) {
            $l0 = (int) $menu['L0'];
            if (!isset($byLevel[$l0])) {
                $byLevel[$l0] = [];
            }
            $byLevel[$l0][$kode] = $menu;
        }

        // Sort each level by KODEMENU
        foreach ($byLevel as $level => $items) {
            ksort($items);
            $byLevel[$level] = $items;
        }

        // Build tree: level 1 items as root
        $tree = [];
        if (isset($byLevel[1])) {
            foreach ($byLevel[1] as $kode => $menu) {
                $tree[] = $this->buildMenuNode($kode, $menu, $byLevel);
            }
        }

        // If no level 1, use level 0 as root
        if (empty($tree) && isset($byLevel[0])) {
            foreach ($byLevel[0] as $kode => $menu) {
                $tree[] = $this->buildMenuNode($kode, $menu, $byLevel);
            }
        }

        usort($tree, fn($a, $b) => strcmp($a['KODEMENU'], $b['KODEMENU']));
        return $tree;
    }

    /**
     * Build a menu node with children from next L0 level
     * Only items at next L0 level with matching KODEMENU prefix become children
     */
    private function buildMenuNode(string $kode, array $menu, array $byLevel): array
    {
        $l0 = (int) $menu['L0'];
        $nextLevel = $l0 + 1;

        $children = [];
        if (isset($byLevel[$nextLevel])) {
            foreach ($byLevel[$nextLevel] as $childKode => $childMenu) {
                // Child must have KODEMENU that starts with parent KODEMENU
                if (str_starts_with($childKode, $kode)) {
                    $children[] = $this->buildMenuNode($childKode, $childMenu, $byLevel);
                }
            }
        }

        return [
            'KODEMENU' => $menu['L1'],
            'NmReport' => $menu['NmReport'],
            'L0' => $menu['L0'],
            'ACCESS' => $menu['KodeReport'],
            'children' => $children
        ];
    }

    /**
     * Get full report configuration (filters, datasets, columns, grouping)
     * Handles KODEMENU normalization (leading zeros stripped)
     */
    public function getReportConfig(string $kodeMenu): ?array
    {
        // Try original code first (preserves leading zeros like 020101)
        $menu = DB::connection('sqlsrv')->selectOne(
            "SELECT * FROM DBMENUREPORT WHERE KODEMENU = ?",
            [$kodeMenu]
        );

        if (!$menu) {
            // Try normalized version
            $normalizedKode = ltrim($kodeMenu, '0');
            $menu = DB::connection('sqlsrv')->selectOne(
                "SELECT * FROM DBMENUREPORT WHERE KODEMENU = ?",
                [$normalizedKode]
            );
        }

        if (!$menu) {
            return null;
        }

        // Pass original kodeMenu to getMasterLaporan (preserves leading zeros)
        $master = $this->getMasterLaporan($kodeMenu);
        if (!$master) {
            // Fallback to normalized
            $master = $this->getMasterLaporan(ltrim($kodeMenu, '0'));
        }
        if (!$master) {
            return [
                'KODEMENU' => $menu->KODEMENU,
                'Keterangan' => $menu->Keterangan,
                'ACCESS' => $menu->ACCESS,
                'filters' => [],
                'datasets' => [],
                'columns' => [],
                'grouping' => null
            ];
        }

        return [
            'id_laporan' => $master->id_laporan,
            'KODEMENU' => $master->KODEMENU,
            'nama_laporan' => $master->nama_laporan,
            'deskripsi' => $master->deskripsi,
            'ACCESS' => $menu->ACCESS,
            'footer_bands' => $this->parseFooterBands($master->footer_bands ?? null),
            'filters' => $this->getFilterParams($master->id_laporan),
            'datasets' => $this->getQueries($master->id_laporan),
            'columns' => $this->getColumnConfig($master->id_laporan),
            'grouping' => $this->getGroupingConfig($master->id_laporan)
        ];
    }

    private function parseFooterBands(?string $json): ?array
    {
        if (empty($json)) {
            return null;
        }
        try {
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get user's default period from dbperiode
     */
    public function getUserDefaultPeriod(string $userId): ?array
    {
        if (empty($userId)) {
            return null;
        }

        try {
            $period = DB::connection('sqlsrv')->selectOne(
                "SELECT BULAN, TAHUN FROM dbperiode WHERE USERID = ?",
                [$userId]
            );

            if ($period) {
                // Calculate first and last day of the period
                $bulan = (int) $period->BULAN;
                $tahun = (int) $period->TAHUN;
                $firstDay = sprintf('%04d-%02d-01', $tahun, $bulan);
                $lastDay = date('Y-m-t', strtotime($firstDay));

                return [
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'tglAwal' => $firstDay,
                    'tglAkhir' => $lastDay
                ];
            }
        } catch (\Exception $e) {
            // Ignore errors, return null
        }

        return null;
    }

    private function getMasterLaporan(string $kodeMenu): ?object
    {
        try {
            // Try original code first (preserves leading zeros)
            $master = DB::connection('sqlsrv')->selectOne(
                "SELECT * FROM dbmasterlaporan WHERE KODEMENU = ? AND status_aktif = 1",
                [$kodeMenu]
            );
            if ($master) return $master;

            // Try with leading zeros stripped
            $normalized = ltrim($kodeMenu, '0');
            if ($normalized !== $kodeMenu) {
                return DB::connection('sqlsrv')->selectOne(
                    "SELECT * FROM dbmasterlaporan WHERE KODEMENU = ? AND status_aktif = 1",
                    [$normalized]
                );
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getFilterParams(int $idLaporan): array
    {
        try {
            $params = DB::connection('sqlsrv')->select(
                "SELECT * FROM dbparameterlaporan WHERE id_laporan = ? ORDER BY posisi",
                [$idLaporan]
            );

            // First pass: collect all filters with their browse configs
            $allFilters = [];
            $filtersByField = []; // index by nama_filter for quick lookup

            foreach ($params as $p) {
                $konfigurasi = null;
                if (!empty($p->konfigurasi)) {
                    try {
                        $konfigurasi = json_decode($p->konfigurasi, true);
                    } catch (\Exception $e) {
                        $konfigurasi = null;
                    }
                }

                $kodeBrowse = $p->kode_browse ?? ($konfigurasi['kode_browse'] ?? null);
                $browseConfig = null;

                if ($kodeBrowse) {
                    $browseConfig = $this->browseService->getConfig($kodeBrowse);
                }

                $filter = [
                    'id_parameter' => $p->id_parameter,
                    'nama_filter' => $p->nama_filter,
                    'label' => $p->label ?? $p->nama_filter,
                    'tipe_input' => $p->tipe_input,
                    'wajib_isi' => (bool) $p->wajib_isi,
                    'nilai_default' => $p->nilai_default,
                    'kode_browse' => $kodeBrowse,
                    'mode' => $konfigurasi['mode'] ?? null,
                    'konfigurasi' => $konfigurasi,
                    'browse_config' => $browseConfig,
                    // Parent filter metadata (will be populated in second pass)
                    'parent_filter_ref' => null,  // reference to parent filter
                    'parent_filter_config' => null, // how to resolve parent value
                ];

                $allFilters[] = $filter;
                $filtersByField[$p->nama_filter] = &$filter;
            }

            // Second pass: resolve parent filter relationships
            foreach ($allFilters as &$filter) {
                $browseConfig = $filter['browse_config'];
                $konfigurasi = $filter['konfigurasi'];

                // Collect parent_filters from both browse_config (dbbrowseconfigs) and
                // konfigurasi (dbparameterlaporan.konfigurasi). Konfigurasi-level parent_filters
                // are used when the browse config has none or when they are more specific
                // (e.g., a custom "parent" field name mapping from Delphi-style configs).
                $parentFiltersList = [];
                if (!empty($browseConfig['parent_filters']) && is_array($browseConfig['parent_filters'])) {
                    $parentFiltersList = $browseConfig['parent_filters'];
                } elseif (is_array($konfigurasi) && !empty($konfigurasi['parent_filters']) && is_array($konfigurasi['parent_filters'])) {
                    // Map konfigurasi-level parent_filters to standard format.
                    // DB-style: [{source_column: 'Perkiraan', operator: '=', type: 'exact'}]
                    // Konfigurasi-style: [{source: 'Perkiraan', target: 'Perkiraan'}]
                    foreach ($konfigurasi['parent_filters'] as $pf) {
                        $mapped = [
                            'source_column' => $pf['source'] ?? $pf['source_column'] ?? null,
                            'operator' => $pf['operator'] ?? '=',
                            'type' => $pf['type'] ?? 'exact',
                        ];
                        // Also capture target for frontend reference
                        if (!empty($pf['target'])) {
                            $mapped['target'] = $pf['target'];
                        }
                        if ($mapped['source_column']) {
                            $parentFiltersList[] = $mapped;
                        }
                    }
                }

                if (empty($parentFiltersList)) {
                    continue;
                }

                foreach ($parentFiltersList as $pf) {
                    $sourceColumn = $pf['source_column'] ?? null;
                    if (!$sourceColumn) {
                        continue;
                    }

                    // Prefer explicit target from konfigurasi parent_filter mapping
                    $parentFilterName = $pf['target'] ?? null;

                    // If no explicit target, try konfigurasi's parent_filter reference
                    if (!$parentFilterName) {
                        $parentFilterName = $konfigurasi['parent_filter'] ?? null;
                    }

                    // Fallback: try to match by source_column naming convention
                    if (!$parentFilterName) {
                        $parentFilterName = $this->inferParentFilterName($sourceColumn);
                    }

                    if ($parentFilterName && isset($filtersByField[$parentFilterName])) {
                        $filter['parent_filter_ref'] = $parentFilterName;
                        $filter['parent_filter_config'] = [
                            'source_column' => $sourceColumn,
                            'operator' => $pf['operator'] ?? '=',
                            'type' => $pf['type'] ?? 'exact',
                            // The actual value will be resolved at runtime from filter values
                            'description' => "Parent value sourced from filter '{$parentFilterName}'",
                        ];
                        break; // Only support one parent for now
                    }
                }
            }

            return $allFilters;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Infer parent filter name from source column name.
     * Converts PascalCase/camelCase to lowercase with common patterns.
     */
    private function inferParentFilterName(string $sourceColumn): ?string
    {
        // Common patterns for grouping hierarchies:
        // KodeGrp → kodeGrup (group -> subgroup)
        // KodeSubGrp → kodeSubGrup (subgroup -> sub-subgroup)
        // Perkiraan → perkiraan
        // KodeBag → kodeBag
        // KodeGdg → kodeGdg

        // Try lowercase first
        $lowercase = strtolower($sourceColumn);

        // Handle common abbreviations
        $patterns = [
            'kodegrp' => 'kodeGrup',
            'kodesubgrp' => 'kodeSubGrup',
            'kodebag' => 'kodeBag',
            'kodebrg' => 'kodeBrg',
            'kodegdg' => 'kodeGdg',
            'perkiraan' => 'perkiraan',
            'kodecustsupp' => 'kodeCustSupp',
            'kodemenu' => 'kodeMenu',
        ];

        return $patterns[$lowercase] ?? $lowercase;
    }

    private function getQueries(int $idLaporan): array
    {
        try {
            $queries = DB::connection('sqlsrv')->select(
                "SELECT * FROM dbquerylaporan WHERE id_laporan = ? ORDER BY urutan",
                [$idLaporan]
            );

            return array_map(fn($q) => [
                'id_query' => $q->id_query,
                'nama_dataset' => $q->nama_dataset,
                'deskripsi' => $q->deskripsi,
                'urutan' => $q->urutan,
                'visible' => (bool) ($q->visible ?? 1),
                'config_json' => json_decode($q->config_json ?? '{}', true),
            ], $queries);
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getColumnConfig(int $idLaporan): array
    {
        try {
            $cols = DB::connection('sqlsrv')->select(
                "SELECT * FROM dbkolomlaporan WHERE id_laporan = ? ORDER BY urutan_tampil",
                [$idLaporan]
            );

            $result = [];
            foreach ($cols as $col) {
                $dataset = $col->nama_dataset;
                if (!isset($result[$dataset])) {
                    $result[$dataset] = [];
                }
                $result[$dataset][] = [
                    'nama_kolom' => $col->nama_kolom,
                    'label_tampil' => $col->label_tampil,
                    'format_type' => $col->format_type,
                    'alignment' => $col->alignment,
                    'is_summable' => (bool) $col->is_summable,
                    'is_visible' => (bool) $col->is_visible
                ];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getGroupingConfig(int $idLaporan): ?array
    {
        try {
            $groups = DB::connection('sqlsrv')->select(
                "SELECT * FROM dbgrouplaporan WHERE id_laporan = ? ORDER BY group_level, sort_order",
                [$idLaporan]
            );

            if (empty($groups)) {
                return null;
            }

            return array_map(fn($g) => [
                'id_group' => $g->id_group,
                'group_level' => $g->group_level,
                'group_field' => $g->group_field,
                'field_value' => $g->field_value,
                'label' => $g->label,
                'sort_order' => $g->sort_order,
                'show_subtotal' => (bool) $g->show_subtotal,
                'style_config' => json_decode($g->style_config ?? '{}', true),
                // NEW: database-driven special handling
                'special_handling' => $g->special_handling ?? 'default',
                'config_json' => json_decode($g->config_json ?? '{}', true)
            ], $groups);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get special handling type from grouping config (database-driven, no hardcoded patterns)
     */
    private function getSpecialHandling(array $grouping): string
    {
        foreach ($grouping as $g) {
            if (!empty($g['special_handling']) && $g['special_handling'] !== 'default') {
                return $g['special_handling'];
            }
        }
        return 'default';
    }

    /**
     * Get special config for the handling type (database-driven)
     */
    private function getSpecialConfig(array $grouping): array
    {
        foreach ($grouping as $g) {
            if (!empty($g['config_json'])) {
                return $g['config_json'];
            }
        }
        return [];
    }

    /**
     * Build groupingConfig from database config (replaces hardcoded determineGroupingStrategy)
     */
    private function buildGroupingConfig(array $grouping): array
    {
        return [
            'specialHandling' => $this->getSpecialHandling($grouping),
            'config' => $this->getSpecialConfig($grouping),
            'groups' => $grouping
        ];
    }

    /**
     * Execute report with multi-dataset and grouping
     */
    public function generateReport(string $kodeMenu, array $filters): array
    {
        try {
            $config = $this->getReportConfig($kodeMenu);

            if (!$config) {
                return ['success' => false, 'message' => 'Report not found'];
            }

            $datasets = [];
            $errors = [];

            // Execute each query in dbquerylaporan
            foreach ($config['datasets'] ?? [] as $dataset) {
                if (!($dataset['visible'] ?? true)) continue;
                try {
                    $data = $this->executeQuery($dataset['nama_dataset'], $dataset['id_query'], $filters);

                    // Compute running balance for datasets that return SaldoAkhir per-row
                    // (mimics .fr3 Pascal script: SaldoAkhir := SaldoAkhir + <saldoakhir>)
                    $data = $this->computeRunningBalance($data, $dataset['nama_dataset']);

                    $datasets[$dataset['nama_dataset']] = $data;
                } catch (\Exception $e) {
                    $errors[] = "Dataset {$dataset['nama_dataset']}: " . $e->getMessage();
                    $datasets[$dataset['nama_dataset']] = [];
                }
            }

            // Apply label mapping for grouping fields (generic for all reports)
            $datasets = $this->applyLabelMapping($datasets, $config['grouping'] ?? []);

            // Build grouped data for ALL datasets that have grouping config
            $groupedData = null;
            $grouping = $config['grouping'] ?? [];

            if (!empty($grouping)) {
                // Build grouped data per dataset
                $groupedData = [];
                foreach ($config['datasets'] ?? [] as $dataset) {
                    $datasetName = $dataset['nama_dataset'];
                    if (!empty($datasets[$datasetName])) {
                        // Get columns for this dataset
                        $datasetColumns = $config['columns'][$datasetName] ?? [];
                        // Build grouping for this dataset
                        $groupedData[$datasetName] = $this->buildGroupedData(
                            $datasets[$datasetName],
                            $grouping,
                            $datasetColumns,
                            $datasetName
                        );
                    }
                }
                // For backward compatibility, also set first dataset as root
                $firstVisible = collect($config['datasets'] ?? [])
                    ->first(fn($d) => ($d['visible'] ?? true));
                if ($firstVisible) {
                    $firstDataset = $firstVisible['nama_dataset'];
                    $groupedData['_main'] = $groupedData[$firstDataset] ?? null;
                }
            }

            // Calculate grand totals
            $grandTotal = $this->calculateGrandTotal($datasets, $config['columns'] ?? []);

            // Build groupingConfig from database (replaces hardcoded determineGroupingStrategy)
            $groupingConfig = $this->buildGroupingConfig($config['grouping'] ?? []);

            return [
                'success' => true,
                'datasets' => $datasets,
                'groupedData' => $groupedData,
                'grandTotal' => $grandTotal,
                'config' => $config,
                // NEW: Full groupingConfig from database (no hardcoded patterns)
                'groupingConfig' => $groupingConfig,
                // REMOVED: groupingStrategy (replaced by detailed groupingConfig)
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Report generation failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' line ' . $e->getLine()
            ];
        }
    }

    private function executeQuery(string $namaDataset, int $idQuery, array $filters): array
    {
        $queryDef = DB::connection('sqlsrv')->selectOne(
            "SELECT query_sumber_data, config_json FROM dbquerylaporan WHERE id_query = ?",
            [$idQuery]
        );

        if (!$queryDef) {
            throw new \Exception("Query definition not found for {$namaDataset}");
        }

        $sql = $queryDef->query_sumber_data;

        // Parse config_json for static parameters and display config.
        // Supports two forms:
        //  1. Flat keys (e.g. {"JenisJurnal": "BKM"}) treated as static params.
        //  2. Nested object (e.g. {"display_role":"detail","static_params":{"Recap":"0"}}).
        //     Keys other than "static_params" are treated as display config.
        $staticParams = [];
        $displayConfig = [];
        if (!empty($queryDef->config_json)) {
            try {
                $decoded = json_decode($queryDef->config_json, true);
                if (is_array($decoded)) {
                    if (isset($decoded['static_params']) && is_array($decoded['static_params'])) {
                        // Nested form: keys outside static_params are display config
                        $staticParams = $decoded['static_params'];
                        foreach ($decoded as $k => $v) {
                            if ($k !== 'static_params') {
                                $displayConfig[$k] = $v;
                            }
                        }
                    } else {
                        // Flat form: all keys are static params (backward compatible)
                        $staticParams = $decoded;
                    }
                }
            } catch (\Exception $e) {
                // Ignore JSON parse errors
            }
        }

        // Replace static params from config_json first (e.g., @JenisJurnal from JenisJurnal)
        // Sort by length DESC for defense in depth (e.g. @kodereport before @kode)
        uksort($staticParams, fn($a, $b) => strlen($b) - strlen($a));
        foreach ($staticParams as $key => $value) {
            $placeholder = '@' . $key;
            if (preg_match('/(?<!\w)' . preg_quote($placeholder, '/') . '(?!\w)/i', $sql)) {
                $sql = preg_replace('/(?<!\w)' . preg_quote($placeholder, '/') . '(?!\w)/i', "'" . addslashes((string) $value) . "'", $sql, 1);
            }
        }

        $params = [];
        $paramValues = [];

        // Replace @param placeholders with explicit values from filters
        $userId = null;
        $droppedFilters = [];

        // Sort filters by placeholder length DESCENDING so that longer placeholders
        // (e.g. @kodesupp1) are replaced BEFORE shorter ones (e.g. @kodesupp).
        // Defense in depth: even though our lookaround regex would never
        // substring-match, this guarantees the replacement order is always correct.
        uksort($filters, fn($a, $b) => strlen($b) - strlen($a));

        foreach ($filters as $key => $value) {
            // Extract user ID if present
            if ($key === 'userId' && $value) {
                $userId = $value;
            }

            $placeholder = '@' . $key;
            if (!preg_match('/(?<!\w)' . preg_quote($placeholder, '/') . '(?!\w)/i', $sql)) {
                // Filter supplied by client but query has no matching @placeholder
                // → it would be silently ignored. Log it so misconfigurations are visible.
                if ($key !== 'userId' && $value !== '' && $value !== null) {
                    $droppedFilters[] = $key;
                }
                continue;
            }

            // Handle array values (multi-select from checkbox mode)
            // Build IN ('a','b','c') clause instead of single value
            if (is_array($value) && count($value) > 0) {
                $escaped = array_map(fn($v) => "'" . addslashes((string) $v) . "'", $value);
                $inClause = implode(',', $escaped);
                $sql = preg_replace('/(?<!\w)' . preg_quote($placeholder, '/') . '(?!\w)/i', $inClause, $sql, 1);
            } elseif (is_array($value) && count($value) === 0) {
                // Empty array → no filter applied. Replace placeholder with NULL
                // so SPs that check `WHERE col = @X OR @X IS NULL` work correctly.
                $sql = preg_replace('/(?<!\w)' . preg_quote($placeholder, '/') . '(?!\w)/i', 'NULL', $sql, 1);
            } elseif ($value === '' || $value === null) {
                // Empty scalar → treat as NULL (no filter applied to SP)
                // Use NULL so stored procedures that check @param IS NULL can return all rows
                $sql = preg_replace('/(?<!\w)' . preg_quote($placeholder, '/') . '(?!\w)/i', 'NULL', $sql, 1);
            } else {
                // Scalar value — escape and replace
                $sql = preg_replace('/(?<!\w)' . preg_quote($placeholder, '/') . '(?!\w)/i', "'" . addslashes((string) $value) . "'", $sql, 1);
            }
        }

        if (!empty($droppedFilters)) {
            \Log::warning('ReportService: filters dropped (not used by query)', [
                'dataset' => $namaDataset,
                'id_query' => $idQuery,
                'dropped' => $droppedFilters,
            ]);
        }

        // For EXEC SP queries: replace any remaining @placeholder that didn't come
        // from filters or static config with NULL. This prevents
        // "Must declare the scalar variable" errors when dbquerylaporan references
        // a parameter (e.g. @Recap in Sp_ReportKartuHutang) that has no
        // matching row in dbparameterlaporan or config_json.
        // Use lookaround (?<!\w)...(?!\w) — NOT \b — to avoid PHP word-boundary
        // inconsistencies around @ and case-insensitive identifier matching.
        if (preg_match('/^\s*EXEC\s+/i', $sql) && preg_match('/(?<!\w)@\w+/i', $sql)) {
            $sql = preg_replace('/(?<!\w)@[A-Za-z_]\w*(?!\w)/', 'NULL', $sql);
        }

        // Handle @IDUser - get from userId filter or from authenticated user
        if (preg_match('/(?<!\w)@IDUser(?!\w)/i', $sql)) {
            if ($userId) {
                $replacement = "'" . addslashes($userId) . "'";
            } else {
                // Try to get from Laravel auth
                try {
                    $userId = auth()->user()->USERID ?? null;
                    $replacement = $userId ? ("'" . addslashes($userId) . "'") : "''";
                } catch (\Exception $e) {
                    $replacement = "''";
                }
            }
            $sql = preg_replace('/(?<!\w)@IDUser(?!\w)/i', $replacement, $sql, 1);
        }

        // Handle @UserID - same source as @IDUser but case may differ from the
        // filter key (`userId`). Match case-insensitively so queries like
        // `EXEC sp_xxx ..., @UserID, 'T'` resolve correctly.
        if (preg_match('/(?<!\w)@UserID(?!\w)/i', $sql)) {
            $resolved = $userId;
            if (!$resolved) {
                try {
                    $resolved = auth()->user()->USERID ?? null;
                } catch (\Exception $e) {
                    $resolved = null;
                }
            }
            $replacement = $resolved ? "'" . addslashes((string) $resolved) . "'" : "''";
            $sql = preg_replace('/(?<!\w)@UserID(?!\w)/i', $replacement, $sql, 1);
        }

        // Execute query directly with substituted values
        try {
            Log::info('[ReportService::executeQuery] dataset=' . $namaDataset . ' | filters=' . json_encode($filters) . ' | sql=' . $sql);

            // Fallback: if any @placeholder remains unreplaced in a SELECT query
            // (no filter or static config provided), remove the entire WHERE clause
            // containing it. This is safer than leaving the @placeholder as-is
            // (which would fail with "Must declare the scalar variable").
            // For EXEC SP queries, residual @placeholders were already replaced
            // with NULL above (so the SP can be invoked with all-null arguments).
            if (preg_match('/(?<!\w)@\w+(?!\w)/', $sql)) {
                Log::warning('[ReportService::executeQuery] residual @placeholder detected in dataset=' . $namaDataset . ' — stripping WHERE clause. sql=' . $sql);
                // Just remove the entire WHERE clause to select all rows.
                // This handles the common case: "WHERE col IN (@X) OR @X IS NULL"
                // strips everything after WHERE up to the next ORDER/GROUP/etc keyword
                $sql = preg_replace(
                    '/\s*WHERE\s+.*?(ORDER|GROUP|HAVING|OPTION|LIMIT|$)/is',
                    ' $1',
                    $sql
                );
                $sql = trim($sql);
                // If we removed too much and have dangling AND/OR at start, clean it
                $sql = preg_replace('/^\s*(AND|OR)\s+/i', '', $sql);
                $sql = preg_replace('/^\s*WHERE\s*$/i', '', $sql);
            }

            $results = DB::connection('sqlsrv')->select($sql);
        } catch (\Exception $e) {
            throw new \Exception("Dataset {$namaDataset}: " . $e->getMessage());
        }

        if (empty($results)) {
            \Log::warning("ReportService dataset {$namaDataset} returned 0 rows", [
                'sql' => $sql,
                'filters' => $filters,
            ]);
        }

        // Convert from Windows-1252 to UTF-8 (only strip null bytes)
        return array_map(function ($row) {
            $converted = [];
            foreach ((array) $row as $key => $value) {
                if (is_string($value)) {
                    // Convert from Windows-1252 (CP1252) to UTF-8
                    $value = mb_convert_encoding($value, 'UTF-8', 'CP1252');
                    // Only remove null bytes, keep everything else including accents and special chars
                    $value = str_replace("\0", '', $value);
                    $value = trim($value);
                    $converted[$key] = $value;
                } else {
                    $converted[$key] = $value;
                }
            }
            return $converted;
        }, $results);
    }

    /**
     * Compute running balance from a per-row saldo field.
     *
     * SPs like sp_ReportBukuTambahan return SaldoAkhir as the net balance
     * of each row (Debet - Kredit), not as a running total. The .fr3
     * Pascal script computes it manually:
     *   SaldoAkhir := SaldoAkhir + <frxDBData."saldoakhir">
     *
     * This method mirrors that logic: it accumulates SaldoAkhir
     * row-by-row and stores the running balance back into the same key
     * so GroupedTable can render it directly.
     */
    private function computeRunningBalance(array $data, string $datasetName): array
    {
        // Only apply to datasets that have a saldo-like column
        if (empty($data)) {
            return $data;
        }

        $deltaCol = isset($data[0]['SaldoRp']) ? 'SaldoRp' : (isset($data[0]['SaldoAkhir']) ? 'SaldoAkhir' : null);
        if ($deltaCol === null) {
            return $data;
        }

        $running = 0.0;
        foreach ($data as &$row) {
            $val = (float)($row[$deltaCol] ?? 0);
            $running += $val;
            $row[$deltaCol] = $running;
        }

        return $data;
    }

    private function buildGroupedData(array $data, array $groupConfig, array $columnConfig, ?string $datasetName = null): array
    {
        // Build label mapping
        $labelMap = [];
        $levelFields = [];
        foreach ($groupConfig as $group) {
            $key = $group['group_level'] . '-' . $group['field_value'];
            $labelMap[$key] = $group['label'];
            $levelFields[$group['group_level']] = $group['group_field'];
        }

        // Defensive: if level 1 is not in dbgrouplaporan but data contains L1 fields,
        // infer it from the lowest group level present in data (e.g. grupAP1)
        if (!isset($levelFields[1]) && !empty($data) && $groupConfig !== null) {
            foreach ($groupConfig as $g) {
                if (isset($g['config_json']) && is_array($g['config_json'])) {
                    $cn = $g['config_json']['field_name'] ?? null;
                    if ($cn && isset($data[0][$cn]) && $data[0][$cn] !== '') {
                        $levelFields[1] = $cn;
                        break;
                    }
                }
            }
        }

        usort($data, function ($a, $b) use ($levelFields) {
            foreach ($levelFields as $level => $field) {
                $cmp = strcmp($a[$field] ?? '', $b[$field] ?? '');
                if ($cmp !== 0) return $cmp;
            }
            return 0;
        });

        // Group data
        $grouped = [];
        $currentLevel1 = null;
        $currentLevel2 = null;

        foreach ($data as $row) {
            $level1Value = $row[$levelFields[1]] ?? '';
            $level2Value = isset($levelFields[2]) ? ($row[$levelFields[2]] ?? '') : '';

            $key1 = '1-' . $level1Value;
            $key2 = '2-' . $level2Value;

            // Level 1
            if (!isset($grouped[$level1Value])) {
                $grouped[$level1Value] = [
                    'label' => $labelMap[$key1] ?? $level1Value,
                    'items' => [],
                    'subgroups' => [],
                    'subtotal' => []
                ];
            }

            // Level 2
            if (!isset($grouped[$level1Value]['subgroups'][$level2Value])) {
                $grouped[$level1Value]['subgroups'][$level2Value] = [
                    'label' => $labelMap[$key2] ?? $level2Value,
                    'items' => [],
                    'subtotal' => []
                ];
            }

            // Add row to level 2
            $grouped[$level1Value]['subgroups'][$level2Value]['items'][] = $row;

            // Update subtotals for level 2
            foreach ($columnConfig as $col) {
                if ($col['is_summable'] && $col['is_visible']) {
                    $colName = $col['nama_kolom'];
                    $val = $this->parseNumber($row[$colName] ?? 0);
                    $grouped[$level1Value]['subgroups'][$level2Value]['subtotal'][$colName] =
                        ($grouped[$level1Value]['subgroups'][$level2Value]['subtotal'][$colName] ?? 0) + $val;
                }
            }
        }

        // Calculate level 1 subtotals
        foreach ($grouped as $l1Key => &$l1Group) {
            foreach ($l1Group['subgroups'] as $l2Group) {
                foreach ($l2Group['subtotal'] as $col => $val) {
                    $l1Group['subtotal'][$col] = ($l1Group['subtotal'][$col] ?? 0) + $val;
                }
            }
        }

        return $grouped;
    }

    private function calculateGrandTotal(array $datasets, array $columnConfig): array
    {
        $total = [];
        $firstDataset = reset($datasets);

        if ($firstDataset) {
            foreach ($columnConfig as $datasetName => $cols) {
                if (empty($datasets[$datasetName])) continue;

                foreach ($cols as $col) {
                    if ($col['is_summable'] && $col['is_visible']) {
                        $colName = $col['nama_kolom'];
                        $sum = 0;
                        foreach ($datasets[$datasetName] as $row) {
                            $sum += $this->parseNumber($row[$colName] ?? 0);
                        }
                        $total[$colName] = $sum;
                    }
                }
            }
        }

        return $total;
    }

    private function parseNumber($value): float
    {
        if (is_numeric($value)) {
            return floatval($value);
        }
        // Handle formatted strings like "1,000,000"
        return floatval(str_replace([' ', ','], '', $value));
    }

    /**
     * Apply label mapping for grouping fields from dbLabelGrup
     * Generic: applies to all datasets that have the grouping field
     */
    private function applyLabelMapping(array $datasets, array $grouping): array
    {
        // Collect all unique grouping fields (no dataset_name in schema)
        $fieldsToMap = [];
        foreach ($grouping as $g) {
            $field = $g['group_field'] ?? null;
            if ($field && !isset($fieldsToMap[$field])) {
                $fieldsToMap[$field] = LabelGrup::getMapping($field);
            }
        }

        // Apply mapping to ALL datasets that have the grouping field
        foreach ($datasets as $datasetName => &$data) {
            foreach ($fieldsToMap as $field => $mapping) {
                foreach ($data as &$row) {
                    $raw = (string)($row[$field] ?? '');
                    $row[$field . '_label'] = $mapping[$raw] ?? $raw;
                }
            }
        }

        return $datasets;
    }
}