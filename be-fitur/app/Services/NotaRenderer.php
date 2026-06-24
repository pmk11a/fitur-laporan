<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * NotaRenderer: Format values for Nota documents.
 *
 * Handles:
 * - Currency formatting (Indonesian: Rp 1.234.567,89)
 * - Date formatting (DD/MM/YYYY, etc.)
 * - Line numbers
 * - Aggregate operations (sum, avg, count, min, max)
 */
class NotaRenderer
{
    public function format($value, array $column): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $type = $column['type'] ?? 'text';

        return match ($type) {
            'currency' => $this->currency($value, $column['decimals'] ?? 2, $column['show_symbol'] ?? true),
            'number' => $this->number($value, $column['decimals'] ?? 0),
            'date' => $this->date($value, $column['format'] ?? 'DD/MM/YYYY'),
            'line_number' => '', // handled at row level
            'text' => (string) $value,
            default => (string) $value,
        };
    }

    public function currency($value, int $decimals = 2, bool $showSymbol = true): string
    {
        $value = (float) $value;
        $formatted = number_format($value, $decimals, ',', '.');
        return $showSymbol ? "Rp {$formatted}" : $formatted;
    }

    public function number($value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    public function date($value, string $format = 'DD/MM/YYYY'): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            $date = $value instanceof Carbon
                ? $value
                : Carbon::parse($value);
        } catch (\Throwable) {
            return (string) $value;
        }

        $phpFormat = match ($format) {
            'DD/MM/YYYY' => 'd/m/Y',
            'MM/DD/YYYY' => 'm/d/Y',
            'YYYY-MM-DD' => 'Y-m-d',
            'DD MMM YYYY' => 'd M Y',
            'DD-MM-YYYY' => 'd-m-Y',
            default => 'd/m/Y',
        };

        return $date->format($phpFormat);
    }

    public function lineNumber(int $index): string
    {
        return (string) ($index + 1);
    }

    public function aggregate(string $op, string $field, array $rows, array $header = []): float
    {
        if (empty($rows)) {
            return 0.0;
        }

        $values = array_map(fn ($row) => (float) ($row[$field] ?? 0), $rows);

        return match ($op) {
            'sum' => array_sum($values),
            'avg' => array_sum($values) / count($values),
            'count' => (float) count($values),
            'min' => min($values),
            'max' => max($values),
            default => 0.0,
        };
    }

    /**
     * Resolve a value_field reference against row data.
     * Supports dotted paths like "perusahaan.NAMA" or "header.Tanggal".
     */
    public function resolveValue(string $field, array $row, array $extra = []): mixed
    {
        if (str_contains($field, '.')) {
            [$scope, $key] = explode('.', $field, 2);
            $source = match ($scope) {
                'perusahaan' => $extra['perusahaan'] ?? [],
                'header' => $extra['header'] ?? $row,
                default => $row,
            };
            return $source[$key] ?? null;
        }

        return $row[$field] ?? null;
    }
}
