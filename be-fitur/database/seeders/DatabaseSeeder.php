<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed dbbrowseconfigs from BrowseService::getConfigMap
        $this->seedBrowseConfigs();
    }

    private function seedBrowseConfigs(): void
    {
        $map = \App\Services\BrowseService::getConfigMap();

        foreach ($map as $kode => $cfg) {
            $addFields = !empty($cfg['additionalFields']) ? json_encode($cfg['additionalFields']) : null;
            $joins = !empty($cfg['joins']) ? json_encode($cfg['joins']) : null;
            $whereExtra = $cfg['whereExtra'] ?? null;
            $aliasFields = !empty($cfg['alias_fields']) ? json_encode($cfg['alias_fields']) : null;
            $parentFilters = !empty($cfg['parent_filters']) ? json_encode($cfg['parent_filters']) : null;

            DB::table('dbbrowseconfigs')->updateOrInsert(
                ['kodebrowse' => $kode],
                [
                    'tablename' => $cfg['table'],
                    'keyfield' => $cfg['keyField'],
                    'labelfield' => $cfg['labelField'],
                    'additionalfields' => $addFields,
                    'joins' => $joins,
                    'whereextra' => $whereExtra,
                    'aliasfields' => $aliasFields,
                    'parentfilters' => $parentFilters,
                    'isactive' => 1,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
