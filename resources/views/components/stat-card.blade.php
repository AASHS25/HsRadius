@props(['title', 'value', 'icon', 'color' => 'primary'])

@php
    $colorMap = [
        'primary' => '#3b82f6',
        'blue' => '#3b82f6',
        'success' => '#22c55e',
        'green' => '#22c55e',
        'warning' => '#f59e0b',
        'orange' => '#f59e0b',
        'purple' => '#8b5cf6',
        'danger' => '#ef4444',
        'red' => '#ef4444',
        'info' => '#06b6d4',
    ];
    $borderColor = $colorMap[$color] ?? $color;
@endphp

<div class="col-xl-3 col-md-6 mb-3">
    <div class="card h-100" style="border-left: 4px solid {{ $borderColor }};">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted mb-1" style="font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">
                        {{ $title }}
                    </div>
                    <div class="fw-bold" style="font-size: 1.5rem; color: #1e293b;">
                        {{ $value }}
                    </div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: {{ $borderColor }}15; display: flex; align-items: center; justify-content: center;">
                    <i class="bi {{ $icon }}" style="font-size: 1.4rem; color: {{ $borderColor }};"></i>
                </div>
            </div>
        </div>
    </div>
</div>
