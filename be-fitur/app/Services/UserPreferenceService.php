<?php

namespace App\Services;

use App\Models\UserPreference;

/**
 * Universal User Preference Service
 *
 * Handles CRUD for all user preferences (format, ui, table, filter, etc.)
 * using a key-value JSON pattern with namespace isolation.
 *
 * Key pattern: {namespace}.{entity}.{sub_entity}
 * Examples:
 *   - format.rep._default
 *   - format.col.020101.penerimaan
 *   - ui.theme
 */
class UserPreferenceService
{
    /**
     * Get a single preference value.
     */
    public function get(int $userId, string $namespace, string $key, mixed $default = null): mixed
    {
        $pref = UserPreference::where('user_id', $userId)
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->first();

        return $pref ? $pref->value : $default;
    }

    /**
     * Get preference with fallback chain:
     *   1. {namespace}.{key}   (specific)
     *   2. {namespace}._default (global default in this namespace)
     *   3. $fallback            (hardcoded fallback)
     */
    public function resolve(int $userId, string $namespace, string $key, mixed $fallback = null): mixed
    {
        // 1. Try specific
        $specific = $this->get($userId, $namespace, $key);
        if ($specific !== null) return $specific;

        // 2. Try namespace default
        $namespaceDefault = $this->get($userId, $namespace, '_default');
        if ($namespaceDefault !== null) return $namespaceDefault;

        // 3. Hardcoded fallback
        return $fallback;
    }

    /**
     * Get all column formats for a specific report, with merged defaults.
     * Returns: ['kode:nama_kolom' => mergedConfig, ...]
     */
    public function getColumnsForReport(int $userId, string $reportCode, array $defaultColumnConfig = []): array
    {
        $all = $this->getNamespace($userId, 'column_format');
        $namespaceDefault = $this->get($userId, 'column_format', '_default', []);

        $result = [];
        foreach ($all as $key => $value) {
            if (str_starts_with($key, $reportCode . ':')) {
                $columnName = substr($key, strlen($reportCode) + 1);
                $result[$columnName] = array_merge($namespaceDefault, $value);
            }
        }

        return $result;
    }

    /**
     * Set (upsert) a preference.
     */
    public function set(int $userId, string $namespace, string $key, mixed $value): UserPreference
    {
        return UserPreference::updateOrCreate(
            ['user_id' => $userId, 'namespace' => $namespace, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Delete a single preference.
     */
    public function delete(int $userId, string $namespace, string $key): bool
    {
        return UserPreference::where('user_id', $userId)
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->delete() > 0;
    }

    /**
     * Get all preferences in a namespace as key=>value array.
     */
    public function getNamespace(int $userId, string $namespace): array
    {
        return UserPreference::where('user_id', $userId)
            ->where('namespace', $namespace)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get ALL preferences grouped by namespace.
     */
    public function getAll(int $userId): array
    {
        return UserPreference::where('user_id', $userId)
            ->get()
            ->groupBy('namespace')
            ->map(fn($items) => $items->pluck('value', 'key')->toArray())
            ->toArray();
    }

    /**
     * Delete an entire namespace for a user.
     */
    public function deleteNamespace(int $userId, string $namespace): int
    {
        return UserPreference::where('user_id', $userId)
            ->where('namespace', $namespace)
            ->delete();
    }

    /**
     * Bulk set: array of {namespace, key, value}.
     */
    public function bulkSet(int $userId, array $items): void
    {
        foreach ($items as $item) {
            $this->set($userId, $item['namespace'], $item['key'], $item['value']);
        }
    }
}
