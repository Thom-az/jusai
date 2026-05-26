{{--
  Modal simples para ações pontuais (1-3 campos).

  Props:
    id        — ID Bootstrap do modal (obrigatório)
    title     — Texto do cabeçalho
    size      — sm | md (padrão) | lg | xl
    centered  — true (padrão) | false
    scrollable — true (padrão) | false

  Slots:
    $slot      — Corpo do modal
    $footer    — Rodapé personalizado (opcional; se omitido mostra botão fechar padrão)

  Uso:
    <x-modal id="modalConfirmar" title="Confirmar exclusão" size="sm">
        <p>Tem certeza que deseja excluir este item?</p>

        <x-slot name="footer">
            <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-danger rounded-pill">Excluir</button>
        </x-slot>
    </x-modal>
--}}

@props([
    'id'         => 'modal',
    'title'      => '',
    'size'       => 'md',
    'centered'   => true,
    'scrollable' => true,
])

@php
    $sizeClass = match($size) {
        'sm'  => 'modal-sm',
        'lg'  => 'modal-lg',
        'xl'  => 'modal-xl',
        default => '',   // md — Bootstrap padrão
    };
    $dialogClasses = trim(implode(' ', array_filter([
        'modal-dialog',
        $sizeClass,
        $centered   ? 'modal-dialog-centered'   : '',
        $scrollable ? 'modal-dialog-scrollable' : '',
    ])));
@endphp

<div
    class="modal fade"
    id="{{ $id }}"
    tabindex="-1"
    aria-labelledby="{{ $id }}Label"
    aria-hidden="true"
>
    <div class="{{ $dialogClasses }}">
        <div class="modal-content jusai-modal-content">

            {{-- Header --}}
            @if($title)
                <div class="modal-header border-0 pb-2">
                    <h5 class="modal-title fw-semibold" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar"></button>
                </div>
            @else
                <div class="d-flex justify-content-end p-3 pb-0">
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar"></button>
                </div>
            @endif

            {{-- Body --}}
            <div class="modal-body pt-2">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if(isset($footer))
                <div class="modal-footer border-0 pt-0 gap-2">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

@once
    @push('styles')
    <style>
    .jusai-modal-content {
        border-radius: 1.5rem;
        border: 1px solid rgba(215, 220, 229, 0.7);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
    }

    [data-theme="dark"] .jusai-modal-content {
        background: #1a1a1a;
        border-color: rgba(255, 255, 255, 0.07);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
    }

    [data-theme="dark"] .modal-title { color: rgba(255, 255, 255, 0.92); }
    [data-theme="dark"] .modal-header .btn-close { filter: invert(1) grayscale(1) brightness(0.8); }
    </style>
    @endpush
@endonce
