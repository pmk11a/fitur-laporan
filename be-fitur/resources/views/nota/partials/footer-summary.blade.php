@php
    $renderer = app(\App\Services\NotaRenderer::class);
@endphp

<table class="footer-summary">
    @foreach($items as $item)
        @php
            $agg = $item['aggregate'] ?? null;
            $value = 0;
            if ($agg) {
                $value = $renderer->aggregate($agg['op'] ?? 'sum', $agg['field'] ?? '', $rows);
            }
            $colType = ['type' => $item['format'] ?? 'number', 'decimals' => $item['decimals'] ?? 2];
            $formattedValue = $renderer->format($value, $colType);
            $label = $item['label'] ?? '';
            if (!empty($item['prefix_field']) && isset($header[$item['prefix_field']])) {
                $label = str_replace('{' . $item['prefix_field'] . '}', $header[$item['prefix_field']], $label);
            }
            $isGrand = !empty($item['bold']);
        @endphp
        <tr class="{{ $isGrand ? 'grand-total' : '' }}">
            <td class="summary-label {{ $isGrand ? 'bold' : '' }}">{{ $label }}</td>
            <td class="summary-value {{ $isGrand ? 'bold' : '' }}">{{ $formattedValue }}</td>
        </tr>
    @endforeach
</table>
