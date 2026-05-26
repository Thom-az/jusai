{{-- Skeleton de linha de listagem. Reproduz a estrutura de .list-item. --}}
@props(['count' => 5])

@php
    $widths = ['62%', '71%', '55%', '78%', '65%', '58%', '74%'];
@endphp

@for ($i = 0; $i < (int) $count; $i++)
    <div class="list-item d-flex align-items-center gap-3 mb-2">
        <div class="skeleton skeleton-circle flex-shrink-0" style="width: 2.25rem; height: 2.25rem"></div>
        <div class="flex-grow-1 min-width-0">
            <div class="skeleton skeleton-text mb-1" style="width: {{ $widths[$i % count($widths)] }}"></div>
            <div class="skeleton skeleton-text" style="width: 40%"></div>
        </div>
        <div class="skeleton skeleton-badge flex-shrink-0" style="width: 5rem"></div>
        <div class="skeleton skeleton-text flex-shrink-0" style="width: 3.5rem; margin-bottom: 0"></div>
    </div>
@endfor
