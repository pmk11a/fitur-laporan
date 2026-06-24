<?php

namespace App\Http\Controllers;

use App\Models\NotaTemplate;
use App\Services\NotaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotaController extends Controller
{
    public function __construct(
        protected NotaService $notaService
    ) {}

    /**
     * Generate Nota PDF.
     *
     * GET /api/nota/{kode}/print?nobukti=X
     */
    public function print(Request $request, string $kode): Response
    {
        $template = $this->notaService->loadTemplate($kode);
        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => "Template '{$kode}' not found",
            ], 404);
        }

        $params = $this->extractParams($request, $template->query_params);

        $data = $this->notaService->buildData($template, $params);

        if (empty($data['header'])) {
            return response()->json([
                'success' => false,
                'message' => 'No data found for given parameters',
            ], 404);
        }

        $data['header'] = $this->notaService->sanitizeArray($data['header']);
        foreach ($data['rows'] as &$row) {
            $row = $this->notaService->sanitizeArray((array) $row);
        }

        $pdf = Pdf::loadView('nota.master', $data);
        $pdf->setPaper($template->paper_size ?? 'A4', $template->orientation ?? 'portrait');

        $filename = $this->makeFilename($kode, $data['header']);

        return $pdf->stream($filename);
    }

    /**
     * Extract query parameters for the template.
     */
    protected function extractParams(Request $request, ?string $queryParams): array
    {
        $expected = [];
        if ($queryParams) {
            $decoded = json_decode($queryParams, true);
            if (is_array($decoded)) {
                $expected = $decoded;
            }
        }

        $params = [];
        foreach ($expected as $key) {
            $value = $request->query($key);
            if ($value !== null) {
                $params[$key] = $value;
            }
        }

        // Default: try to grab any @-style params from the query string
        foreach ($request->query() as $key => $value) {
            if (!isset($params[$key]) && !in_array($key, ['_', 'format'])) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Build filename for download: NOTA_JUAL_NOBUKTI.pdf
     */
    protected function makeFilename(string $kode, array $header): string
    {
        $nobukti = $header['nobukti'] ?? $header['NOBUKTI'] ?? 'preview';
        $safeNobukti = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $nobukti);
        return "{$kode}_{$safeNobukti}.pdf";
    }
}
