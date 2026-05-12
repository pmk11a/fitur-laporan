<?php

namespace App\Services;

use App\Models\{{ModelName}};
use App\Models\{{RelatedModels}};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * {{ModelName}} Service
 *
 * Generated from: {{DelphiForm}}
 * Contains business logic extracted from Delphi procedures
 *
 * @package App\Services
 */
class {{ModelName}}Service
{
    {{ConstructorDependencies}}

    // =========================================================================
    // CRUD OPERATIONS
    // =========================================================================

    /**
     * Create a new {{ModelNameLower}} record
     *
     * From Delphi: btTambahClick / FormCreate
     *
     * @param array $data
     * @return {{ModelName}}
     * @throws Exception
     */
    public function create(array $data): {{ModelName}}
    {
        {{Validations}}

        return DB::transaction(function () use ($data) {
            {{NumberGeneration}}

            ${{ModelNameLower}} = {{ModelName}}::create($data);

            {{RelatedDataCreation}}

            {{LoggingActivity}}

            return ${{ModelNameLower}};
        });
    }

    /**
     * Update an existing {{ModelNameLower}} record
     *
     * From Delphi: btEditClick / btSimpanClick
     *
     * @param {{ModelName}} ${{ModelNameLower}}
     * @param array $data
     * @return {{ModelName}}
     * @throws Exception
     */
    public function update({{ModelName}} ${{ModelNameLower}}, array $data): {{ModelName}}
    {
        {{UpdateValidations}}

        return DB::transaction(function () use (${{ModelNameLower}}, $data) {
            ${{ModelNameLower}}->update($data);

            {{RelatedDataUpdate}}

            {{LoggingActivityUpdate}}

            return ${{ModelNameLower}};
        });
    }

    /**
     * Delete a {{ModelNameLower}} record
     *
     * From Delphi: btHapusClick with CekDelete validation
     *
     * @param {{ModelName}} ${{ModelNameLower}}
     * @return bool
     * @throws Exception
     */
    public function delete({{ModelName}} ${{ModelNameLower}}): bool
    {
        {{DeleteValidation}}

        return DB::transaction(function () use (${{ModelNameLower}}) {
            {{RelatedDataDeletion}}

            {{LoggingActivityDelete}}

            return ${{ModelNameLower}}->delete();
        });
    }

    // =========================================================================
    // VALIDATION METHODS (From Cek* functions)
    // =========================================================================

    {{ValidationMethods}}

    // =========================================================================
    // BUSINESS LOGIC METHODS (From Delphi procedures)
    // =========================================================================

    {{BusinessLogicMethods}}

    // =========================================================================
    // NUMBER GENERATION (From Check_Nomor, etc.)
    // =========================================================================

    {{NumberGenerationMethods}}

    // =========================================================================
    // LOGGING (From LoggingData procedure)
    // =========================================================================

    /**
     * Log activity to database
     *
     * From Delphi: LoggingData procedure
     *
     * @param string $action
     * @param string $source
     * @param string $noBukti
     * @param string|null $keterangan
     * @return void
     */
    private function logActivity(
        string $action,
        string $source,
        string $noBukti,
        ?string $keterangan = null
    ): void {
        DB::table('dbLogFile')->insert([
            'Tahun' => Carbon::now()->year,
            'Bulan' => Carbon::now()->month,
            'Tanggal' => Carbon::now(),
            'Pemakai' => auth()->id() ?? 'system',
            'Aktivitas' => $action,
            'Sumber' => $source,
            'NoBukti' => $noBukti,
            'Keterangan' => $keterangan,
        ]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    {{HelperMethods}}
}
