<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * {Module} Service
 *
 * Description of what this service does.
 * Migrated from: Delphi Frm{Xxx}
 */
class {Module}Service
{
    /**
     * Get data by user ID
     * Migrated from: Delphi FunctionName
     *
     * @param string $userId
     * @return array|null
     */
    public function getData(string $userId): ?array
    {
        // ⚠️ PENTING: Column name UPPERCASE untuk SQL Server
        $data = DB::table('{TableName}')
            ->where('UserID', $userId)
            ->first();

        if (!$data) {
            return null;
        }

        return [
            'field' => $data->COLUMN_NAME,
        ];
    }

    /**
     * Check if data exists
     *
     * @param string $userId
     * @return bool
     */
    public function exists(string $userId): bool
    {
        return DB::table('{TableName}')
            ->where('UserID', $userId)
            ->exists();
    }

    /**
     * Save or update data
     * Migrated from: Delphi FunctionName
     *
     * @param string $userId
     * @param array $data
     * @return void
     */
    public function save(string $userId, array $data): void
    {
        $exists = $this->exists($userId);

        if ($exists) {
            DB::table('{TableName}')
                ->where('UserID', $userId)
                ->update($data);
        } else {
            DB::table('{TableName}')->insert(array_merge([
                'UserID' => $userId,
            ], $data));
        }
    }

    /**
     * Delete data
     *
     * @param string $userId
     * @return void
     */
    public function delete(string $userId): void
    {
        DB::table('{TableName}')
            ->where('UserID', $userId)
            ->delete();
    }

    /**
     * Validate data
     *
     * @param array $data
     * @return array
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Add validation logic here
        if (empty($data['field'])) {
            $errors['field'] = 'Field is required';
        }

        return $errors;
    }
}
