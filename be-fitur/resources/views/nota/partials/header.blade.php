@php
    $renderer = app(\App\Services\NotaRenderer::class);
@endphp

<div class="nota-header clearfix">
    <div class="nota-header-left">
        @if(!empty($config['logo_path']))
            <img src="{{ public_path($config['logo_path']) }}" class="nota-logo" alt="Logo">
        @endif

        @if(!empty($config['company_field']))
            @php $companyName = $renderer->resolveValue($config['company_field'], $header, ['perusahaan' => $perusahaan]); @endphp
            <div class="nota-company-name">{{ $companyName }}</div>
        @endif

        @if(!empty($config['company_address_field']))
            @php $companyAddr = $renderer->resolveValue($config['company_address_field'], $header, ['perusahaan' => $perusahaan]); @endphp
            <div class="nota-company-addr">{{ $companyAddr }}</div>
        @endif
    </div>

    <div class="nota-header-right">
        @if(!empty($config['right_block']))
            <table class="info-table">
                @foreach($config['right_block'] as $rb)
                    <tr>
                        <td class="info-label" style="width: 30%;">{{ $rb['label'] ?? '' }}</td>
                        <td class="info-value">
                            @php
                                $val = $rb['value_field'] ?? '';
                                $value = $val ? ($header[$val] ?? '') : '';
                                if (!empty($rb['format'])) {
                                    $value = $renderer->format($value, ['type' => 'date', 'format' => $rb['format']]);
                                }
                                $value .= $rb['suffix'] ?? '';
                                if (!empty($rb['suffix2_field'])) {
                                    $value .= ($header[$rb['suffix2_field']] ?? '') . ($rb['suffix2'] ?? '');
                                }
                            @endphp
                            {{ $value }}
                        </td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    @if(!empty($config['title']))
        <div class="nota-title" style="clear: both;">{{ $config['title'] }}</div>
    @endif
</div>
