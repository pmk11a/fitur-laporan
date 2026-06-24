@php
    $renderer = app(\App\Services\NotaRenderer::class);
@endphp

<table class="detail-table">
    <thead>
        <tr>
            @foreach($columns as $col)
                <th class="text-{{ $col['align'] ?? 'left' }}"
                    @if(!empty($col['width'])) style="width: {{ $col['width'] }};" @endif>
                    {{ $col['label'] ?? $col['field'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $idx => $row)
            <tr>
                @foreach($columns as $col)
                    <td class="text-{{ $col['align'] ?? 'left' }}">
                        @if(($col['type'] ?? 'text') === 'line_number')
                            {{ $renderer->lineNumber($idx) }}
                        @else
                            {{ $renderer->format($row[$col['field']] ?? '', $col) }}
                        @endif
                    </td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) }}" class="text-center" style="padding: 8mm;">
                    (Tidak ada data)
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
