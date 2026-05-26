{{-- Skeleton de card de métrica. Reproduz a estrutura de .metric-card / .stat-card. --}}
@props(['class' => ''])

<div class="metric-card {{ $class }}">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div class="flex-grow-1 min-width-0">
            <div class="skeleton skeleton-text mb-2" style="width: 52%"></div>
            <div class="skeleton mb-2" style="width: 45%; height: 1.85rem; border-radius: 0.5rem"></div>
            <div class="skeleton skeleton-text" style="width: 68%"></div>
        </div>
        <div class="skeleton skeleton-circle flex-shrink-0" style="width: 3rem; height: 3rem"></div>
    </div>
</div>
