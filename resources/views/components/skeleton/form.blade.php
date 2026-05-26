{{-- Skeleton de formulário. Reproduz a estrutura de um surface-card com campos. --}}
@props([
    'fields' => 4,
    'hasButton' => true,
    'class'  => '',
])

<div class="surface-card p-4 {{ $class }}">
    @for ($i = 0; $i < (int) $fields; $i++)
        <div class="mb-3">
            <div class="skeleton skeleton-text mb-1" style="width: {{ [28, 35, 22, 40, 30][$i % 5] }}%"></div>
            <div class="skeleton" style="height: 2.5rem; border-radius: 0.6rem; width: 100%"></div>
        </div>
    @endfor

    @if ($hasButton)
        <div class="mt-4">
            <div class="skeleton skeleton-btn rounded-pill" style="width: 9rem"></div>
        </div>
    @endif
</div>
