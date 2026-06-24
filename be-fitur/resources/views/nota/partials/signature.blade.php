<div class="signatures">
    @foreach($signatures as $sig)
        <div class="signature-block" style="text-align: {{ $sig['position'] ?? 'center' }}; float: {{ $sig['position'] === 'right' ? 'right' : 'left' }};">
            <div class="signature-label">{{ $sig['label'] ?? '' }}</div>
            <div class="signature-space">&nbsp;</div>
            <div>( ___________________ )</div>
            @if(!empty($sig['caption']))
                <div style="font-size: 8pt;">{{ $sig['caption'] }}</div>
            @endif
        </div>
    @endforeach
    <div style="clear: both;"></div>
</div>
