{{--
  Modal com stepper para criação de itens complexos (4+ campos com agrupamento lógico).

  Props:
    id          — ID único do modal Bootstrap (obrigatório)
    title       — Título do modal
    subtitle    — Subtítulo opcional
    steps       — Array associativo: [['label' => '...'], ...]
    size        — lg (padrão) | xl
    draftKey    — Chave localStorage para persistir rascunho (opcional)

  Slots:
    $steps_content  — Conteúdo de cada passo via @slot('step_1') … @slot('step_N')
    $footer_back    — Substituir botão Voltar (opcional)
    $footer_next    — Substituir botão Próximo (opcional)

  Uso:
    <x-modal-stepper
        id="modalNovoCaso"
        title="Novo caso"
        subtitle="Preencha os dados para criar um caso jurídico."
        :steps="[['label'=>'Identificação'], ['label'=>'Classificação'], ['label'=>'Documentos'], ['label'=>'Confirmação']]"
    >
        <x-slot name="step_1"> … </x-slot>
        <x-slot name="step_2"> … </x-slot>
        <x-slot name="step_3"> … </x-slot>
        <x-slot name="step_4"> … </x-slot>
    </x-modal-stepper>
--}}

@props([
    'id'       => 'modalStepper',
    'title'    => 'Novo item',
    'subtitle' => null,
    'steps'    => [['label' => 'Passo 1'], ['label' => 'Passo 2']],
    'size'     => 'lg',
    'draftKey' => null,
])

@php
    $stepCount = count($steps);
    $modalSize = $size === 'xl' ? 'modal-xl' : 'modal-lg';
    $draftAttr  = $draftKey ? "draftKey: '{{ $draftKey }}'" : "draftKey: null";
@endphp

<div
    class="modal fade"
    id="{{ $id }}"
    tabindex="-1"
    aria-labelledby="{{ $id }}Label"
    aria-hidden="true"
>
    <div class="modal-dialog {{ $modalSize }} modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-stepper-content"
             x-data="{
                 current: 1,
                 total: {{ $stepCount }},
                 {{ $draftAttr }},

                 next() {
                     if (this.current < this.total) this.current++;
                     if (this.draftKey) this.saveDraft();
                 },
                 back() { if (this.current > 1) this.current--; },
                 reset() { this.current = 1; if (this.draftKey) this.clearDraft(); },

                 saveDraft() {
                     try { localStorage.setItem(this.draftKey, JSON.stringify({ step: this.current })); } catch(e) {}
                 },
                 loadDraft() {
                     if (!this.draftKey) return;
                     try {
                         const d = JSON.parse(localStorage.getItem(this.draftKey) || '{}');
                         if (d.step && d.step <= this.total) this.current = d.step;
                     } catch(e) {}
                 },
                 clearDraft() {
                     try { if (this.draftKey) localStorage.removeItem(this.draftKey); } catch(e) {}
                 }
             }"
             x-init="loadDraft()"
             @if($draftKey)
             x-on:hide.bs.modal="clearDraft()"
             @endif
        >

            {{-- Header --}}
            <div class="modal-header border-0 pb-0 pe-3">
                <div class="flex-grow-1">
                    <h5 class="modal-title fw-semibold mb-1" id="{{ $id }}Label">{{ $title }}</h5>
                    @if($subtitle)
                        <p class="text-secondary small mb-3">{{ $subtitle }}</p>
                    @else
                        <div class="mb-3"></div>
                    @endif

                    {{-- Stepper indicator --}}
                    <div class="modal-stepper-bar">
                        @foreach ($steps as $i => $step)
                            @php $num = $i + 1; @endphp
                            <div class="stepper-step"
                                 :class="{
                                     'active'    : current === {{ $num }},
                                     'completed' : current > {{ $num }}
                                 }"
                                 data-step="{{ $num }}">
                                <div class="stepper-dot">
                                    <span class="stepper-dot-num">{{ $num }}</span>
                                    <i class="bi bi-check stepper-dot-check" aria-hidden="true"></i>
                                </div>
                                <div class="stepper-label d-none d-sm-block">{{ $step['label'] }}</div>
                            </div>
                            @if (!$loop->last)
                                <div class="stepper-line"
                                     :class="{ 'completed': current > {{ $num }} }"></div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <button type="button"
                        class="btn-close align-self-start ms-3"
                        data-bs-dismiss="modal"
                        aria-label="Fechar"
                        x-on:click="reset()"></button>
            </div>

            {{-- Body — painéis de cada passo em track deslizante --}}
            <div class="modal-body pt-3 stepper-body">
                <div class="stepper-track" :style="`transform: translateX(${-(current - 1) * 100}%)`">
                    @for ($n = 1; $n <= $stepCount; $n++)
                        <div class="stepper-panel">
                            {{ ${'step_' . $n} ?? '' }}
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer border-0 pt-0 gap-2">
                <button type="button"
                        class="btn btn-outline-secondary rounded-pill px-4"
                        x-show="current > 1"
                        x-on:click="back()">
                    <i class="bi bi-chevron-left me-1"></i>Voltar
                </button>

                <button type="button"
                        class="btn btn-primary rounded-pill px-4"
                        x-show="current < total"
                        x-on:click="next()">
                    Próximo <i class="bi bi-chevron-right ms-1"></i>
                </button>

                {{-- Slot para botão de submissão no último passo --}}
                <div x-show="current === total">
                    {{ $submit ?? '' }}
                </div>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
    <style>
    .modal-stepper-content {
        border-radius: 1.5rem;
        border: 1px solid rgba(215, 220, 229, 0.7);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
    }

    .stepper-body {
        overflow: hidden;
        padding-left: 0;
        padding-right: 0;
    }

    .stepper-track {
        display: flex;
        transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
        will-change: transform;
    }

    .stepper-panel {
        flex: 0 0 100%;
        width: 100%;
        min-width: 0;
        padding: 0 1rem;
    }

    .modal-stepper-bar {
        display: flex;
        align-items: center;
        gap: 0;
        margin-bottom: 0.25rem;
    }

    .stepper-step {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .stepper-dot {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background: rgba(215, 220, 229, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        color: #6b7280;
        transition: background 0.22s, color 0.22s;
        position: relative;
    }

    .stepper-dot-check { display: none; font-size: 0.85rem; }

    .stepper-step.active   .stepper-dot { background: var(--jusai-action); color: #fff; }
    .stepper-step.completed .stepper-dot {
        background: rgba(15, 118, 110, 0.15);
        color: var(--jusai-success);
    }
    .stepper-step.completed .stepper-dot-num  { display: none; }
    .stepper-step.completed .stepper-dot-check { display: inline; }

    .stepper-label {
        font-size: 0.72rem;
        font-weight: 500;
        color: #94a3b8;
        white-space: nowrap;
    }
    .stepper-step.active .stepper-label    { color: var(--jusai-action); font-weight: 600; }
    .stepper-step.completed .stepper-label { color: var(--jusai-success); }

    .stepper-line {
        flex: 1;
        height: 2px;
        background: rgba(215, 220, 229, 0.7);
        margin: 0 0.35rem;
        border-radius: 999px;
        transition: background 0.22s;
        min-width: 1rem;
    }
    .stepper-line.completed { background: rgba(15, 118, 110, 0.35); }

    /* Dark mode */
    [data-theme="dark"] .modal-stepper-content {
        background: #1a1a1a;
        border-color: rgba(255,255,255,0.07);
        box-shadow: 0 24px 60px rgba(0,0,0,0.55);
    }
    [data-theme="dark"] .stepper-dot       { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.35); }
    [data-theme="dark"] .stepper-step.active .stepper-dot { background: #3767d4; color: #fff; }
    [data-theme="dark"] .stepper-label     { color: rgba(255,255,255,0.3); }
    [data-theme="dark"] .stepper-step.active .stepper-label { color: #60a5fa; }
    [data-theme="dark"] .stepper-line      { background: rgba(255,255,255,0.08); }
    </style>
    @endpush
@endonce
