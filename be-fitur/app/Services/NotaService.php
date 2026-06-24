<?php

namespace App\Services;

use App\Models\NotaTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * NotaService: Loads template config, executes queries, returns data for rendering.
 */
class NotaService
{
    public function __construct(
        protected NotaRenderer $renderer
    ) {}

    /**
     * Load template configuration.
     * @param string $kodeNota Template code (e.g. 'NOTA_JUAL')
     */
    public function loadTemplate(string $kodeNota): ?NotaTemplate
    {
        return NotaTemplate::where('kode_nota', $kodeNota)
            ->where('aktif', 1)
            ->first();
    }

    /**
     * Build data array for Blade rendering.
     *
     * @param NotaTemplate $template
     * @param array<string, mixed> $params Query parameters (e.g. ['nobukti' => 'JL260001'])
     */
    public function buildData(NotaTemplate $template, array $params): array
    {
        $perusahaan = $this->getCompanyInfo();

        // Header data
        $header = $template->query_header
            ? $this->runSingleQuery($template->query_header, $params)
            : [];

        // Detail rows
        $rows = $template->query_detail
            ? $this->runSingleQuery($template->query_detail, $params, true)
            : [];

        // Apply conditional substitutions from config
        $config = $template->config_json;
        if (isset($config['conditional'])) {
            foreach ($config['conditional'] as $rule) {
                $this->applyCondition($rule, $header);
            }
        }

        return compact('template', 'config', 'header', 'rows', 'perusahaan');
    }

    /**
     * Execute a single SELECT query and return result.
     * Supports @PARAM bindings (replaced with named bindings before execution).
     */
    protected function runSingleQuery(string $sql, array $params, bool $fetchAll = false): array
    {
        [$finalSql, $bindings] = $this->resolveBindings($sql, $params);

        $result = DB::connection('sqlsrv')->select($finalSql, $bindings);

        if (!$fetchAll) {
            return $result[0] ?? [];
        }

        return $result;
    }

    /**
     * Resolve @PARAM bindings in SQL strings into Laravel bindings.
     */
    protected function resolveBindings(string $sql, array $params): array
    {
        $bindings = [];
        $finalSql = preg_replace_callback('/@(\w+)/', function ($match) use ($params, &$bindings) {
            $key = $match[1];
            if (!array_key_exists($key, $params)) {
                return $match[0];
            }
            $bindings[] = $params[$key];
            return '?';
        }, $sql);

        return [$finalSql, $bindings];
    }

    /**
     * Get company (perusahaan) info.
     */
    protected function getCompanyInfo(): array
    {
        $result = DB::connection('sqlsrv')
            ->table('dbPerusahaan')
            ->select('KodePerusahaan', 'NAMA', 'Alamat', 'Telp', 'Fax', 'Email')
            ->first();

        return $result ? (array) $result : [
            'NAMA' => 'PT. PERTAMINA',
            'Alamat' => '',
            'Telp' => '',
        ];
    }

    /**
     * Apply conditional substitution to header data.
     */
    protected function applyCondition(array $rule, array &$data): void
    {
        if (!isset($rule['if'], $rule['then'])) {
            return;
        }

        $field = $rule['if']['field'] ?? '';
        $equals = $rule['if']['equals'] ?? null;
        $actual = $data[$field] ?? null;

        if ($actual == $equals) {
            $target = $rule['target_field'] ?? $field;
            $data[$target] = $rule['then'];
        }
    }

    /**
     * Convert PHP value for Blade (Carbon, stdClass, etc. to strings/arrays).
     */
    public function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value instanceof Carbon) {
                $data[$key] = $value->format('Y-m-d');
            } elseif (is_object($value) && method_exists($value, '__toString')) {
                $data[$key] = (string) $value;
            } elseif (is_object($value)) {
                $data[$key] = (array) $value;
            }
        }

        return $data;
    }
}
