{{--
  Aviso de conteúdo gerado por IA — reutilizável em qualquer view.

  Props:
    variant  — banner (padrão): faixa compacta no topo/rodapé de seções
               inline: linha simples de texto (dentro de cards, modais)
               toast: caixa menor com ícone proeminente
    class    — classes adicionais

  Uso:
    <x-ai-disclaimer />                          — banner padrão
    <x-ai-disclaimer variant="inline" />         — linha de texto
    <x-ai-disclaimer variant="toast" />          — caixa destacada
--}}

@props([
    'variant' => 'banner',
    'class'   => '',
])

@php
    $notice = config('jusai.ai.review_notice',
        'Conteúdo gerado por IA para apoio operacional. Revisão humana por profissional habilitado é obrigatória.');
@endphp

@if ($variant === 'inline')

    <span class="ai-disclaimer-inline {{ $class }}">
        <i class="bi bi-cpu me-1" aria-hidden="true"></i>{{ $notice }}
    </span>

@elseif ($variant === 'toast')

    <div class="ai-disclaimer-toast {{ $class }}" role="note" aria-label="Aviso de IA">
        <div class="ai-disclaimer-toast-icon">
            <i class="bi bi-cpu" aria-hidden="true"></i>
        </div>
        <p class="mb-0 small">{{ $notice }}</p>
    </div>

@else {{-- banner --}}

    <div class="ai-disclaimer-banner {{ $class }}" role="note" aria-label="Aviso de IA">
        <i class="bi bi-cpu flex-shrink-0" aria-hidden="true"></i>
        <span>{{ $notice }}</span>
    </div>

@endif

@once
    @push('styles')
    <style>
    /* --- Banner --- */
    .ai-disclaimer-banner {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.6rem 0.9rem;
        border-radius: 0.75rem;
        background: rgba(37, 99, 235, 0.06);
        border: 1px solid rgba(37, 99, 235, 0.14);
        font-size: 0.78rem;
        color: #4b5563;
        line-height: 1.55;
    }

    .ai-disclaimer-banner i {
        font-size: 0.85rem;
        color: var(--jusai-action);
        margin-top: 0.05rem;
    }

    /* --- Inline --- */
    .ai-disclaimer-inline {
        font-size: 0.72rem;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .ai-disclaimer-inline i {
        font-size: 0.78rem;
        color: var(--jusai-action);
        opacity: 0.7;
    }

    /* --- Toast --- */
    .ai-disclaimer-toast {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border-radius: 1rem;
        background: rgba(37, 99, 235, 0.06);
        border: 1px solid rgba(37, 99, 235, 0.14);
        color: #4b5563;
    }

    .ai-disclaimer-toast-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 0.5rem;
        background: rgba(37, 99, 235, 0.10);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--jusai-action);
        font-size: 1rem;
    }

    /* Dark mode */
    [data-theme="dark"] .ai-disclaimer-banner,
    [data-theme="dark"] .ai-disclaimer-toast {
        background: rgba(96, 165, 250, 0.07);
        border-color: rgba(96, 165, 250, 0.18);
        color: rgba(255, 255, 255, 0.55);
    }

    [data-theme="dark"] .ai-disclaimer-banner i,
    [data-theme="dark"] .ai-disclaimer-toast-icon {
        color: #60a5fa;
    }

    [data-theme="dark"] .ai-disclaimer-toast-icon {
        background: rgba(96, 165, 250, 0.12);
        color: #60a5fa;
    }

    [data-theme="dark"] .ai-disclaimer-inline {
        color: rgba(255, 255, 255, 0.32);
    }

    [data-theme="dark"] .ai-disclaimer-inline i {
        color: #60a5fa;
    }
    </style>
    @endpush
@endonce
