@php
    $renderer = app(\App\Services\NotaRenderer::class);
@endphp

<table class="info-table">
    <tr>
        @foreach($items as $item)
            <td style="width: {{ 100 / count($items) }}%;">
                <span class="info-label">{{ $item['label'] ?? '' }}:</span>
                @php
                    $val = $header[$item['value_field'] ?? ''] ?? '';
                    if (!empty($item['format'])) {
                        $val = $renderer->format($val, ['type' => 'date', 'format' => $item['format']]);
                    }
                    $val .= $item['suffix'] ?? '';
                    if (!empty($item['suffix2_field'])) {
                        $val .= ($header[$item['suffix2_field']] ?? '') . ($item['suffix2'] ?? '');
                    }
                @endphp
                <span class="info-value">{{ $val }}</span>
            </td>
        @endforeach
    </tr>
</table>
