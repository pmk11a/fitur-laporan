<div class="kepada-yth">
    @if(!empty($config['label']))
        <div class="kepada-yth-label">{{ $config['label'] }}</div>
    @endif
    @foreach($config['fields'] ?? [] as $field)
        <div>{{ $header[$field] ?? '' }}</div>
    @endforeach
</div>
