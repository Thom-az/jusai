{{-- Skeleton de card kanban. Reproduz a estrutura de um card de coluna. --}}
@props(['class' => ''])

<div class="surface-card p-3 {{ $class }}" style="border-radius: 1rem">
    <div class="skeleton skeleton-text mb-2" style="width: 78%"></div>
    <div class="skeleton skeleton-text mb-3" style="width: 56%"></div>
    <div class="d-flex align-items-center justify-content-between gap-2">
        <div class="skeleton skeleton-circle" style="width: 1.75rem; height: 1.75rem"></div>
        <div class="skeleton skeleton-badge" style="width: 4.5rem"></div>
    </div>
</div>
