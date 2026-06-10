<?php

namespace App\Services;

use App\Models\DBMasterLaporan;
use App\Models\DBQueryLaporan;
use App\Models\DBKolomLaporan;
use App\Models\DBGroupLaporan;
use App\Models\DBParameterLaporan;
use App\Models\DBMENUREPORT;
use App\Models\FLMENUREPORT;
use App\Models\FLPASS;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    // ============================================================
    // REPORTS (Master)
    // ============================================================

    public function getAllReports(): array
    {
        $reports = DB::connection('sqlsrv')->select("
            SELECT m.id_laporan, m.KODEMENU, m.nama_laporan, m.deskripsi,
                   m.status_aktif, m.footer_bands, m.created_at, m.updated_at,
                   menu.Keterangan, menu.L0
            FROM dbmasterlaporan m
            LEFT JOIN DBMENUREPORT menu ON menu.KODEMENU = m.KODEMENU
            ORDER BY m.nama_laporan
        ");

        return array_map(fn($r) => $this->mapReport($r), $reports);
    }

    public function getReport(int $id): ?array
    {
        $report = DB::connection('sqlsrv')->selectOne(
            "SELECT m.*, menu.Keterangan, menu.L0
             FROM dbmasterlaporan m
             LEFT JOIN DBMENUREPORT menu ON menu.KODEMENU = m.KODEMENU
             WHERE m.id_laporan = ?", [$id]
        );

        if (!$report) return null;

        return array_merge($this->mapReport($report), [
            'filters' => $this->getFilters($id),
            'datasets' => $this->getDatasets($id),
            'columns' => $this->getAllColumns($id),
            'groups' => $this->getGroups($id),
            'access' => $this->getUserAccess($report->KODEMENU),
        ]);
    }

    public function createReport(array $data): array
    {
        $id = DB::connection('sqlsrv')->table('dbmasterlaporan')->insertGetId([
            'KODEMENU' => $data['KODEMENU'],
            'nama_laporan' => $data['nama_laporan'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'status_aktif' => $data['status_aktif'] ?? true,
            'footer_bands' => $data['footer_bands'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['id_laporan' => $id, ...$data];
    }

    public function updateReport(int $id, array $data): bool
    {
        $update = array_filter([
            'nama_laporan' => $data['nama_laporan'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
            'status_aktif' => $data['status_aktif'] ?? null,
            'footer_bands' => $data['footer_bands'] ?? null,
            'updated_at' => now(),
        ], fn($v) => $v !== null);

        return DB::connection('sqlsrv')->table('dbmasterlaporan')
            ->where('id_laporan', $id)->update($update) > 0;
    }

    public function deleteReport(int $id): bool
    {
        $report = DB::connection('sqlsrv')->table('dbmasterlaporan')
            ->where('id_laporan', $id)->first();
        if (!$report) return false;

        DB::connection('sqlsrv')->table('dbparameterlaporan')->where('id_laporan', $id)->delete();
        DB::connection('sqlsrv')->table('dbquerylaporan')->where('id_laporan', $id)->delete();
        DB::connection('sqlsrv')->table('dbkolomlaporan')->where('id_laporan', $id)->delete();
        DB::connection('sqlsrv')->table('dbgrouplaporan')->where('id_laporan', $id)->delete();

        return DB::connection('sqlsrv')->table('dbmasterlaporan')
            ->where('id_laporan', $id)->delete() > 0;
    }

    public function getAvailableKodeMenu(): array
    {
        $used = DB::connection('sqlsrv')->select(
            "SELECT KODEMENU FROM dbmasterlaporan WHERE KODEMENU IS NOT NULL"
        );
        $usedCodes = array_column($used, 'KODEMENU');

        $all = DB::connection('sqlsrv')->select(
            "SELECT KODEMENU, Keterangan FROM DBMENUREPORT WHERE L0 >= 1 ORDER BY KODEMENU"
        );

        return array_values(array_filter($all, fn($m) => !in_array($m->KODEMENU, $usedCodes)));
    }

    // ============================================================
    // FILTERS (Parameters)
    // ============================================================

    public function getFilters(int $idLaporan): array
    {
        $filters = DB::connection('sqlsrv')->select(
            "SELECT * FROM dbparameterlaporan WHERE id_laporan = ? ORDER BY posisi",
            [$idLaporan]
        );

        return array_map(fn($f) => $this->mapFilter($f), $filters);
    }

    public function createFilter(int $idLaporan, array $data): array
    {
        $maxPosisi = DB::connection('sqlsrv')->selectOne(
            "SELECT MAX(posisi) as m FROM dbparameterlaporan WHERE id_laporan = ?",
            [$idLaporan]
        );
        $posisi = ($maxPosisi->m ?? -1) + 1;

        $id = DB::connection('sqlsrv')->table('dbparameterlaporan')->insertGetId([
            'id_laporan' => $idLaporan,
            'nama_filter' => $data['nama_filter'],
            'label' => $data['label'] ?? $data['nama_filter'],
            'tipe_input' => $data['tipe_input'],
            'wajib_isi' => $data['wajib_isi'] ?? false,
            'nilai_default' => $data['nilai_default'] ?? null,
            'posisi' => $data['posisi'] ?? $posisi,
            'konfigurasi' => $data['konfigurasi'] ?? null,
            'created_at' => now(),
        ]);

        return ['id_parameter' => $id, 'id_laporan' => $idLaporan, ...$data];
    }

    public function updateFilter(int $id, array $data): bool
    {
        $update = array_filter([
            'nama_filter' => $data['nama_filter'] ?? null,
            'label' => $data['label'] ?? null,
            'tipe_input' => $data['tipe_input'] ?? null,
            'wajib_isi' => $data['wajib_isi'] ?? null,
            'nilai_default' => $data['nilai_default'] ?? null,
            'posisi' => $data['posisi'] ?? null,
            'konfigurasi' => $data['konfigurasi'] ?? null,
        ], fn($v) => $v !== null);

        return DB::connection('sqlsrv')->table('dbparameterlaporan')
            ->where('id_parameter', $id)->update($update) > 0;
    }

    public function deleteFilter(int $id): bool
    {
        return DB::connection('sqlsrv')->table('dbparameterlaporan')
            ->where('id_parameter', $id)->delete() > 0;
    }

    public function reorderFilters(int $idLaporan, array $orders): bool
    {
        foreach ($orders as $order) {
            if (!isset($order['id'], $order['posisi'])) continue;
            DB::connection('sqlsrv')->table('dbparameterlaporan')
                ->where('id_parameter', $order['id'])
                ->where('id_laporan', $idLaporan)
                ->update(['posisi' => (int) $order['posisi']]);
        }
        return true;
    }

    // ============================================================
    // DATASETS (Queries)
    // ============================================================

    public function getDatasets(int $idLaporan): array
    {
        $datasets = DB::connection('sqlsrv')->select(
            "SELECT * FROM dbquerylaporan WHERE id_laporan = ? ORDER BY urutan",
            [$idLaporan]
        );

        return array_map(fn($d) => [
            'id_query' => $d->id_query,
            'id_laporan' => $d->id_laporan,
            'nama_dataset' => $d->nama_dataset,
            'deskripsi' => $d->deskripsi,
            'query_sumber_data' => $d->query_sumber_data,
            'urutan' => $d->urutan,
            'visible' => (bool) ($d->visible ?? 1),
            'config_json' => json_decode($d->config_json ?? '{}', true),
        ], $datasets);
    }

    public function createDataset(int $idLaporan, array $data): array
    {
        $maxUrutan = DB::connection('sqlsrv')->selectOne(
            "SELECT MAX(urutan) as m FROM dbquerylaporan WHERE id_laporan = ?",
            [$idLaporan]
        );
        $urutan = ($maxUrutan->m ?? 0) + 1;

        $id = DB::connection('sqlsrv')->table('dbquerylaporan')->insertGetId([
            'id_laporan' => $idLaporan,
            'nama_dataset' => $data['nama_dataset'],
            'query_sumber_data' => $data['query_sumber_data'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'urutan' => $data['urutan'] ?? $urutan,
            'config_json' => isset($data['config_json']) && $data['config_json']
                ? json_encode($data['config_json'])
                : null,
            'visible' => $data['visible'] ?? true,
        ]);

        return [
            'id_query' => $id,
            'id_laporan' => $idLaporan,
            'nama_dataset' => $data['nama_dataset'],
            'query_sumber_data' => $data['query_sumber_data'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'urutan' => $data['urutan'] ?? $urutan,
            'visible' => $data['visible'] ?? true,
            'config_json' => $data['config_json'] ?? null,
        ];
    }

    public function updateDataset(int $id, array $data): bool
    {
        $update = array_filter([
            'nama_dataset' => $data['nama_dataset'] ?? null,
            'query_sumber_data' => $data['query_sumber_data'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
            'urutan' => $data['urutan'] ?? null,
        ], fn($v) => $v !== null);

        if (isset($data['config_json'])) {
            $update['config_json'] = $data['config_json']
                ? json_encode($data['config_json'])
                : null;
        }

        if (array_key_exists('visible', $data)) {
            $update['visible'] = (bool) $data['visible'];
        }

        return DB::connection('sqlsrv')->table('dbquerylaporan')
            ->where('id_query', $id)->update($update) > 0;
    }

    public function deleteDataset(int $id): bool
    {
        return DB::connection('sqlsrv')->table('dbquerylaporan')
            ->where('id_query', $id)->delete() > 0;
    }

    public function previewQuery(string $sql, array $filters = []): array
    {
        if (empty(trim($sql))) {
            return ['success' => false, 'message' => 'Query is empty'];
        }

        // Substitute @placeholders with dummy values for preview
        $previewSql = $this->substituteParams($sql, $filters);

        try {
            $results = DB::connection('sqlsrv')->select(
                "SET FMTONLY ON; {$previewSql}; SET FMTONLY OFF;"
            );

            if (empty($results)) {
                return ['success' => true, 'columns' => [], 'rows' => [], 'message' => 'Query returned no data'];
            }

            $columns = array_keys((array) $results[0]);
            $rows = array_map(fn($r) => (array) $r, array_slice($results, 0, 5));

            return [
                'success' => true,
                'columns' => $columns,
                'rows' => $rows,
                'rowCount' => count($results),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function substituteParams(string $sql, array $filters): string
    {
        // Replace @ParamName placeholders with dummy safe values
        foreach ($filters as $key => $value) {
            $placeholder = '@' . $key;
            if (str_contains($sql, $placeholder)) {
                $dummy = is_array($value) && count($value) > 0
                    ? "'" . addslashes($value[0]) . "'"
                    : (is_numeric($value) ? "1" : "'preview'");
                $sql = str_replace($placeholder, $dummy, $sql);
            }
        }
        return $sql;
    }

    // ============================================================
    // COLUMNS
    // ============================================================

    public function getAllColumns(int $idLaporan): array
    {
        $cols = DB::connection('sqlsrv')->select(
            "SELECT * FROM dbkolomlaporan WHERE id_laporan = ? ORDER BY nama_dataset, urutan_tampil",
            [$idLaporan]
        );

        $result = [];
        foreach ($cols as $col) {
            $dataset = $col->nama_dataset;
            if (!isset($result[$dataset])) $result[$dataset] = [];
            $result[$dataset][] = $this->mapColumn($col);
        }
        return $result;
    }

    public function createColumn(int $idLaporan, array $data): array
    {
        $id = DB::connection('sqlsrv')->table('dbkolomlaporan')->insertGetId([
            'id_laporan' => $idLaporan,
            'nama_dataset' => $data['nama_dataset'],
            'nama_kolom' => $data['nama_kolom'],
            'label_tampil' => $data['label_tampil'],
            'urutan_tampil' => $data['urutan_tampil'] ?? 0,
            'format_type' => $data['format_type'] ?? 'text',
            'alignment' => $data['alignment'] ?? 'left',
            'is_summable' => $data['is_summable'] ?? false,
            'is_visible' => $data['is_visible'] ?? true,
        ]);

        return ['id_kolom' => $id, 'id_laporan' => $idLaporan, ...$data];
    }

    public function updateColumn(int $id, array $data): bool
    {
        $update = array_filter([
            'nama_dataset' => $data['nama_dataset'] ?? null,
            'nama_kolom' => $data['nama_kolom'] ?? null,
            'label_tampil' => $data['label_tampil'] ?? null,
            'urutan_tampil' => $data['urutan_tampil'] ?? null,
            'format_type' => $data['format_type'] ?? null,
            'alignment' => $data['alignment'] ?? null,
            'is_summable' => $data['is_summable'] ?? null,
            'is_visible' => $data['is_visible'] ?? null,
        ], fn($v) => $v !== null);

        return DB::connection('sqlsrv')->table('dbkolomlaporan')
            ->where('id_kolom', $id)->update($update) > 0;
    }

    public function deleteColumn(int $id): bool
    {
        return DB::connection('sqlsrv')->table('dbkolomlaporan')
            ->where('id_kolom', $id)->delete() > 0;
    }

    // ============================================================
    // GROUPING
    // ============================================================

    public function getGroups(int $idLaporan): array
    {
        $groups = DB::connection('sqlsrv')->select(
            "SELECT * FROM dbgrouplaporan WHERE id_laporan = ? ORDER BY group_level, sort_order",
            [$idLaporan]
        );

        return array_map(fn($g) => $this->mapGroup($g), $groups);
    }

    public function createGroup(int $idLaporan, array $data): array
    {
        $id = DB::connection('sqlsrv')->table('dbgrouplaporan')->insertGetId([
            'id_laporan' => $idLaporan,
            'group_level' => $data['group_level'],
            'group_field' => $data['group_field'] ?? '',
            'field_value' => $data['field_value'] ?? '',
            'label' => $data['label'],
            'sort_order' => $data['sort_order'] ?? 0,
            'show_subtotal' => $data['show_subtotal'] ?? true,
            'style_config' => $data['style_config'] ?? null,
            'special_handling' => $data['special_handling'] ?? 'default',
            'config_json' => $data['config_json'] ?? null,
        ]);

        return ['id_group' => $id, 'id_laporan' => $idLaporan, ...$data];
    }

    public function updateGroup(int $id, array $data): bool
    {
        $update = array_filter([
            'group_level' => $data['group_level'] ?? null,
            'group_field' => $data['group_field'] ?? null,
            'field_value' => $data['field_value'] ?? null,
            'label' => $data['label'] ?? null,
            'sort_order' => $data['sort_order'] ?? null,
            'show_subtotal' => $data['show_subtotal'] ?? null,
            'style_config' => $data['style_config'] ?? null,
            'special_handling' => $data['special_handling'] ?? null,
            'config_json' => $data['config_json'] ?? null,
        ], fn($v) => $v !== null);

        return DB::connection('sqlsrv')->table('dbgrouplaporan')
            ->where('id_group', $id)->update($update) > 0;
    }

    public function deleteGroup(int $id): bool
    {
        return DB::connection('sqlsrv')->table('dbgrouplaporan')
            ->where('id_group', $id)->delete() > 0;
    }

    // ============================================================
    // MENU ITEMS
    // ============================================================

    public function getMenuItems(): array
    {
        $items = DB::connection('sqlsrv')->select(
            "SELECT * FROM DBMENUREPORT ORDER BY L0, KODEMENU"
        );

        return array_map(fn($m) => [
            'KODEMENU' => $m->KODEMENU,
            'Keterangan' => $m->Keterangan,
            'L0' => $m->L0,
            'ACCESS' => $m->ACCESS,
            'OL' => $m->OL,
            'icon' => $m->icon,
            'PlatformMask' => $m->PlatformMask,
        ], $items);
    }

    public function updateMenuItem(string $kodeMenu, array $data): bool
    {
        $update = array_filter([
            'Keterangan' => $data['Keterangan'] ?? null,
            'L0' => $data['L0'] ?? null,
            'icon' => $data['icon'] ?? null,
            'OL' => $data['OL'] ?? null,
        ], fn($v) => $v !== null);

        if (empty($update)) return false;

        return DB::connection('sqlsrv')->table('DBMENUREPORT')
            ->where('KODEMENU', $kodeMenu)->update($update) > 0;
    }

    // ============================================================
    // USER ACCESS
    // ============================================================

    public function getUserAccess(string $kodeMenu): array
    {
        $access = DB::connection('sqlsrv')->select(
            "SELECT a.*, p.FullName
             FROM DBFLMENUREPORT a
             LEFT JOIN DBFLPASS p ON p.USERID = a.USERID
             WHERE a.L1 = ?", [$kodeMenu]
        );

        return array_map(fn($a) => [
            'USERID' => $a->UserID,
            'FullName' => $a->FullName ?? $a->UserID,
            'Access' => (bool) $a->Access,
            'IsDesign' => (bool) $a->IsDesign,
            'IsExport' => (bool) $a->Isexport,
        ], $access);
    }

    public function grantAccess(string $kodeMenu, array $data): array
    {
        DB::connection('sqlsrv')->table('DBFLMENUREPORT')->updateOrInsert(
            ['USERID' => $data['USERID'], 'L1' => $kodeMenu],
            [
                'Access' => $data['Access'] ?? true,
                'IsDesign' => $data['IsDesign'] ?? false,
                'IsExport' => $data['IsExport'] ?? false,
            ]
        );

        return $this->getUserAccess($kodeMenu);
    }

    public function revokeAccess(string $kodeMenu, string $userId): bool
    {
        return DB::connection('sqlsrv')->table('DBFLMENUREPORT')
            ->where('L1', $kodeMenu)->where('USERID', $userId)->delete() > 0;
    }

    public function getAllUsers(): array
    {
        $users = DB::connection('sqlsrv')->select(
            "SELECT USERID, FullName FROM DBFLPASS ORDER BY USERID"
        );

        return array_map(fn($u) => [
            'USERID' => $u->USERID,
            'FullName' => $u->FullName ?? $u->USERID,
        ], $users);
    }

    // ============================================================
    // MAPPERS
    // ============================================================

    private function mapReport($r): array
    {
        return [
            'id_laporan' => $r->id_laporan,
            'KODEMENU' => $r->KODEMENU,
            'nama_laporan' => $r->nama_laporan,
            'deskripsi' => $r->deskripsi,
            'status_aktif' => (bool) $r->status_aktif,
            'footer_bands' => $this->parseJson($r->footer_bands),
            'created_at' => $r->created_at,
            'updated_at' => $r->updated_at,
            'Keterangan' => $r->Keterangan ?? null,
            'L0' => $r->L0 ?? null,
            'icon' => $r->icon ?? null,
        ];
    }

    private function mapFilter($f): array
    {
        return [
            'id_parameter' => $f->id_parameter,
            'id_laporan' => $f->id_laporan,
            'nama_filter' => $f->nama_filter,
            'label' => $f->label ?? $f->nama_filter,
            'tipe_input' => $f->tipe_input,
            'wajib_isi' => (bool) $f->wajib_isi,
            'nilai_default' => $f->nilai_default,
            'posisi' => $f->posisi,
            'konfigurasi' => $this->parseJson($f->konfigurasi),
        ];
    }

    private function mapColumn($c): array
    {
        return [
            'id_kolom' => $c->id_kolom,
            'id_laporan' => $c->id_laporan,
            'nama_dataset' => $c->nama_dataset,
            'nama_kolom' => $c->nama_kolom,
            'label_tampil' => $c->label_tampil,
            'urutan_tampil' => $c->urutan_tampil,
            'format_type' => $c->format_type,
            'alignment' => $c->alignment,
            'is_summable' => (bool) $c->is_summable,
            'is_visible' => (bool) $c->is_visible,
        ];
    }

    private function mapGroup($g): array
    {
        return [
            'id_group' => $g->id_group,
            'id_laporan' => $g->id_laporan,
            'group_level' => $g->group_level,
            'group_field' => $g->group_field,
            'field_value' => $g->field_value,
            'label' => $g->label,
            'sort_order' => $g->sort_order,
            'show_subtotal' => (bool) $g->show_subtotal,
            'style_config' => $this->parseJson($g->style_config),
            'special_handling' => $g->special_handling ?? 'default',
            'config_json' => $this->parseJson($g->config_json),
        ];
    }

    private function parseJson(?string $json)
    {
        if (empty($json)) return null;
        try {
            return json_decode($json, true);
        } catch (\Exception $e) {
            return null;
        }
    }
}