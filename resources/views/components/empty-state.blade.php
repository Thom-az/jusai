{{--
  Estado vazio educativo — explica o CONCEITO do que vai aparecer aqui, não só "lista vazia".

  Props:
    icon            — Ícone Bootstrap Icons (ex: 'bi-folder2-open')
    title           — Título do estado vazio
    description     — Texto educativo: o que é esta seção, para que serve, por que ainda está vazia
    primaryAction   — Array: ['label' => '...', 'href' => '...'] ou ['label' => '...', 'modal' => '#id']
    secondaryAction — Array: ['label' => '...', 'href' => '...'] (opcional)
    size            — sm | md (padrão) | lg
    muted           — true: visual mais discreto (ex: dentro de drawer ou card menor)

  Uso:
    <x-empty-state
        icon="bi-folder2-open"
        title="Nenhum dossiê ainda"
        description="Os casos jurídicos do seu escritório aparecem aqui. Cada dossiê reúne documentos, análises de IA e o histórico completo do processo."
        :primary-action="['label' => 'Criar primeiro caso', 'href' => route('cases.create')]"
        :secondary-action="['label' => 'Saiba mais', 'href' => '#']"
    />
--}}

@props([
    'icon'            => 'bi-inbox',
    'title'           => 'Nada por aqui ainda',
    'description'     => '',
    'primaryAction'   => null,
    'secondaryAction' => null,
    'size'            => 'md',
    'muted'           => false,
])

@php
    $iconSize   = match($size) { 'sm' => '2rem', 'lg' => '3.5rem', default => '2.75rem' };
    $titleSize  = match($size) { 'sm' => 'fs-6', 'lg' => 'fs-4',  default => 'fs-5'    };
    $padding    = match($size) { 'sm' => 'py-4', 'lg' => 'py-6',  default => 'py-5'    };
    $btnSize    = $size === 'sm' ? 'btn-sm' : '';
    $iconOpacity = $muted ? '0.28' : '0.38';
@endphp

<div class="empty-state-wrap {{ $padding }} text-center mx-auto" style="max-width: {{ $size === 'lg' ? '460px' : '360px' }}">
    <div class="empty-state-icon mb-3" style="opacity: {{ $iconOpacity }}">
        <i class="bi {{ $icon }}" style="font-size: {{ $iconSize }}; color: var(--jusai-action)"></i>
    </div>

    <h6 class="{{ $titleSize }} fw-semibold mb-2 empty-state-title">{{ $title }}</h6>

    @if($description)
        <p class="text-secondary small mb-0 empty-state-desc" style="line-height: 1.6">
            {{ $description }}
        </p>
    @endif

    @if($primaryAction || $secondaryAction)
        <div class="d-flex align-items-center justify-content-center gap-2 mt-4 flex-wrap">
            @if($primaryAction)
                @if(isset($primaryAction['href']))
                    <a href="{{ $primaryAction['href'] }}"
                       @if(!str_starts_with($primaryAction['href'], '#')) wire:navigate @endif
                       class="btn btn-primary rounded-pill px-4 {{ $btnSize }}">
                        @if(isset($primaryAction['icon']))
                            <i class="bi {{ $primaryAction['icon'] }} me-2"></i>
                        @endif
                        {{ $primaryAction['label'] }}
                    </a>
                @elseif(isset($primaryAction['modal']))
                    <button type="button"
                            class="btn btn-primary rounded-pill px-4 {{ $btnSize }}"
                            data-bs-toggle="modal"
                            data-bs-target="{{ $primaryAction['modal'] }}">
                        @if(isset($primaryAction['icon']))
                            <i class="bi {{ $primaryAction['icon'] }} me-2"></i>
                        @endif
                        {{ $primaryAction['label'] }}
                    </button>
                @endif
            @endif

            @if($secondaryAction)
                <a href="{{ $secondaryAction['href'] ?? '#' }}"
                   class="btn btn-outline-secondary rounded-pill px-4 {{ $btnSize }}">
                    {{ $secondaryAction['label'] }}
                </a>
            @endif
        </div>
    @endif
</div>

@once
    @push('styles')
    <style>
    .empty-state-wrap {
        width: 100%;
    }

    .empty-state-icon i {
        display: block;
        line-height: 1;
    }

    .empty-state-title {
        color: var(--jusai-text, #0f172a);
    }

    [data-theme="dark"] .empty-state-title {
        color: rgba(255, 255, 255, 0.85);
    }

    [data-theme="dark"] .empty-state-icon i {
        color: #60a5fa !important;
    }

    .py-6 { padding-top: 3.5rem !important; padding-bottom: 3.5rem !important; }
    </style>
    @endpush
@endonce
