<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * GenericBrowseService — database-driven browse configuration
 *
 * Stores browse config in dbbrowseconfigs table.
 * Supports both table-based (tablename set) and query-based (query field) configs.
 *
 * SOLID:
 * - S: Single responsibility — database-driven browse configs only
 * - O: Add new browse types via DB INSERT, no code changes needed
 * - L: Extends BrowseService contract (same config array shape)
 * - D: Depends on DB abstraction, not hardcoded arrays
 */
class GenericBrowseService
{
    /**
     * Find a browse config by kodeBrowse.
     */
    public function find(string $kodeBrowse): ?array
    {
        $row = DB::table('dbbrowseconfigs')
            ->where('kodebrowse', $kodeBrowse)
            ->where('isactive', 1)
            ->first();

        if (!$row) {
            return null;
        }

        return $this->normalizeRow($row);
    }

    /**
     * Get all active browse configs.
     */
    public function all(): array
    {
        $rows = DB::table('dbbrowseconfigs')
            ->where('isactive', 1)
            ->orderBy('kodebrowse')
            ->get();

        return $rows->map(fn($row) => $this->normalizeRow($row))->toArray();
    }

    /**
     * Store or update a browse config.
     *
     * @param string $kodeBrowse
     * @param array $data {
     *   tablename?,    -- NULL = pakai query custom
     *   keyField,      -- field kode
     *   labelField,   -- field nama/label
     *   query?,       -- SQL lengkap / SP call (kalau tablename NULL)
     *   additionalFields?, joins?, whereExtra?,
     *   aliasFields?, parentFilters?, params?, isactive?
     * }
     */
    public function upsert(string $kodeBrowse, array $data): void
    {
        $exists = DB::table('dbbrowseconfigs')
            ->where('kodebrowse', $kodeBrowse)
            ->exists();

        // Normalize additional fields to PascalCase for consistency with SQL output
        $addFieldsInput = $data['additionalFields'] ?? $data['additionalfields'] ?? [];
        if (!empty($addFieldsInput)) {
            $addFieldsInput = array_map(function($f) {
                return empty($f) ? $f : ucfirst(strtolower($f));
            }, $addFieldsInput);
        }
        $addFields = !empty($addFieldsInput)
            ? json_encode($addFieldsInput)
            : null;
        $joins = !empty($data['joins'] ?? null)
            ? json_encode($data['joins'])
            : null;
        $aliasFields = !empty($data['alias_fields'] ?? $data['aliasFields'] ?? null)
            ? json_encode($data['alias_fields'] ?? $data['aliasFields'])
            : null;
        $parentFilters = !empty($data['parent_filters'] ?? $data['parentFilters'] ?? null)
            ? json_encode($data['parent_filters'] ?? $data['parentFilters'])
            : null;
        $params = !empty($data['params'] ?? null)
            ? json_encode($data['params'])
            : null;

        $row = [
            'kodebrowse' => $kodeBrowse,
            'tablename' => $data['table'] ?? $data['tablename'] ?? null,
            'keyfield' => $data['keyField'] ?? $data['keyfield'] ?? null,
            'labelfield' => $data['labelField'] ?? $data['labelfield'] ?? null,
            'query' => $data['query'] ?? null,
            'additionalfields' => $addFields,
            'joins' => $joins,
            'whereextra' => $data['whereExtra'] ?? $data['whereextra'] ?? null,
            'aliasfields' => $aliasFields,
            'parentfilters' => $parentFilters,
            'params' => $params,
            'isactive' => $data['isactive'] ?? 1,
            'updated_at' => now(),
        ];

        if ($exists) {
            DB::table('dbbrowseconfigs')
                ->where('kodebrowse', $kodeBrowse)
                ->update($row);
        } else {
            $row['created_at'] = now();
            DB::table('dbbrowseconfigs')->insert($row);
        }
    }

    /**
     * Soft-delete (deactivate) a browse config.
     */
    public function deactivate(string $kodeBrowse): void
    {
        DB::table('dbbrowseconfigs')
            ->where('kodebrowse', $kodeBrowse)
            ->update(['isactive' => 0, 'updated_at' => now()]);
    }

    /**
     * Normalize DB row to same shape as BrowseService::getConfigMap() values.
     */
    private function normalizeRow($row): array
    {
        $row = (array) $row;

        $config = [
            'kodeBrowse' => $row['kodebrowse'],
            'table' => $row['tablename'],
            'keyField' => $row['keyfield'],
            'labelField' => $row['labelfield'],
            'query' => $row['query'] ?? null,
            'additionalFields' => json_decode($row['additionalfields'] ?? '[]', true) ?: [],
            'joins' => json_decode($row['joins'] ?? '[]', true) ?: [],
            'whereExtra' => $row['whereextra'] ?? '',
            'alias_fields' => json_decode($row['aliasfields'] ?? '{}', true) ?: [],
            'parent_filters' => json_decode($row['parentfilters'] ?? '[]', true) ?: [],
            'params' => json_decode($row['params'] ?? '[]', true) ?: [],
        ];

        // Clean up nulls / empty strings
        if (empty($config['whereExtra'])) {
            $config['whereExtra'] = null;
        }
        if (empty($config['joins'])) {
            $config['joins'] = null;
        }
        if (empty($config['alias_fields'])) {
            unset($config['alias_fields']);
        }
        if (empty($config['parent_filters'])) {
            unset($config['parent_filters']);
        }
        if (empty($config['params'])) {
            unset($config['params']);
        }
        if (empty($config['query'])) {
            unset($config['query']);
        }

        return $config;
    }
}
