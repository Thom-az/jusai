{{--
  Drawer lateral (slide-over à direita) para visualizar/editar registros.

  Props:
    id        — ID único do drawer (obrigatório)
    title     — Título do cabeçalho
    subtitle  — Subtítulo opcional
    width     — md (padrão) | lg | xl
    withNav   — true: exibe botões ← → para navegar entre registros

  Slots:
    $slot     — Conteúdo principal
    $footer   — Rodapé opcional (ações)

  Controle via Alpine (x-data no pai ou em qualquer ancestral):
    $dispatch('open-drawer', { id: 'meuDrawer' })
    $dispatch('close-drawer', { id: 'meuDrawer' })

  Uso:
    <x-drawer id="drawerCaso" title="Detalhes do caso" :with-nav="true">
        …conteúdo…
        <x-slot name="footer">
            <a href="…" class="btn btn-primary rounded-pill">Abrir completo</a>
        </x-slot>
    </x-drawer>

    // Trigger em qualquer lugar da página:
    // <button @click="$dispatch('open-drawer', { id: 'drawerCaso' })">Ver detalhes</button>
--}}

@props([
    'id'       => 'drawer',
    'title'    => '',
    'subtitle' => null,
    'width'    => 'md',
    'withNav'  => false,
])

@php
    $widthMap = ['md' => '420px', 'lg' => '580px', 'xl' => '720px'];
    $drawerWidth = $widthMap[$width] ?? '420px';
@endphp

<div
    x-data="{ open: false }"
    x-on:open-drawer.window="if ($event.detail?.id === '{{ $id }}') open = true"
    x-on:close-drawer.window="if ($event.detail?.id === '{{ $id }}') open = false"
    x-on:keydown.escape.window="open = false"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    :aria-hidden="!open"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="open = false"
        class="drawer-backdrop"
        aria-hidden="true"
    ></div>

    {{-- Painel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="drawer-panel"
        style="width: {{ $drawerWidth }}; max-width: 96vw;"
    >
        {{-- Header --}}
        <div class="drawer-header">
            <div class="flex-grow-1 min-width-0">
                @if($title)
                    <h5 class="fw-semibold mb-0 text-truncate" id="{{ $id }}-title">{{ $title }}</h5>
                @endif
                @if($subtitle)
                    <div class="text-secondary small mt-1 text-truncate">{{ $subtitle }}</div>
                @endif
            </div>

            @if($withNav)
                <div class="d-flex gap-1 flex-shrink-0">
                    <button type="button"
                            class="btn shell-icon-button btn-sm"
                            x-on:click="$dispatch('drawer-prev', { id: '{{ $id }}' })"
                            title="Anterior">
                        <i class="bi bi-chevron-up" aria-hidden="true"></i>
                    </button>
                    <button type="button"
                            class="btn shell-icon-button btn-sm"
                            x-on:click="$dispatch('drawer-next', { id: '{{ $id }}' })"
                            title="Próximo">
                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                </div>
            @endif

            <button type="button"
                    class="btn shell-icon-button flex-shrink-0"
                    x-on:click="open = false"
                    aria-label="Fechar">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="drawer-body">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
            <div class="drawer-footer">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>

@once
    @push('styles')
    <style>
    .drawer-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        z-index: 1040;
        backdrop-filter: blur(2px);
    }

    .drawer-panel {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        z-index: 1045;
        display: flex;
        flex-direction: column;
        background: rgba(255, 255, 255, 0.97);
        border-left: 1px solid rgba(215, 220, 229, 0.9);
        box-shadow: -24px 0 60px rgba(15, 23, 42, 0.14);
        overflow: hidden;
    }

    .drawer-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1.1rem 1.25rem 1rem;
        border-bottom: 1px solid rgba(215, 220, 229, 0.7);
        flex-shrink: 0;
    }

    .drawer-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
        overscroll-behavior: contain;
    }

    .drawer-footer {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.9rem 1.25rem;
        border-top: 1px solid rgba(215, 220, 229, 0.7);
        flex-shrink: 0;
    }

    /* Transitions via Alpine — precisa das classes Tailwind ou CSS custom */
    .translate-x-full  { transform: translateX(100%); }
    .translate-x-0     { transform: translateX(0); }
    .transition        { transition-property: transform, opacity; }
    .ease-out          { transition-timing-function: cubic-bezier(0,0,.2,1); }
    .ease-in           { transition-timing-function: cubic-bezier(.4,0,1,1); }
    .duration-200      { transition-duration: 200ms; }
    .duration-250      { transition-duration: 250ms; }
    .duration-150      { transition-duration: 150ms; }
    .transition-opacity { transition-property: opacity; }

    /* Dark mode */
    [data-theme="dark"] .drawer-backdrop  { background: rgba(0,0,0,0.6); }
    [data-theme="dark"] .drawer-panel {
        background: #1a1a1a;
        border-left-color: rgba(255,255,255,0.07);
        box-shadow: -24px 0 60px rgba(0,0,0,0.55);
    }
    [data-theme="dark"] .drawer-header    { border-bottom-color: rgba(255,255,255,0.07); }
    [data-theme="dark"] .drawer-footer    { border-top-color: rgba(255,255,255,0.07); }
    [data-theme="dark"] .drawer-header h5 { color: rgba(255,255,255,0.92); }
    </style>
    @endpush
@endonce
