@props(['items' => []])
<div class="fh-adm-stats">
    @foreach ($items as $stat)
        <div class="fh-adm-stat" data-accent="{{ $stat['accent'] ?? 'primary' }}">
            <span class="fh-adm-stat-label">{{ $stat['label'] }}</span>
            <div class="fh-adm-stat-value">{{ $stat['value'] }}</div>
        </div>
    @endforeach
</div>
