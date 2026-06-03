@extends('layouts.app')

@section('title', 'Chat — ' . $caso->title)

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('cases.show', $caso) }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar ao caso
            </a>
            <div>
                <h2 class="fw-semibold mb-1">Assistente Jurídico</h2>
                <p class="text-secondary mb-0 small">
                    <i class="bi bi-briefcase me-1"></i>{{ $caso->title }}
                </p>
            </div>
            <span class="badge text-bg-secondary ms-auto">
                <i class="bi bi-cpu me-1"></i>IA
            </span>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="surface-card p-0 overflow-hidden">
                    <livewire:caso-chat :caso="$caso" />
                </div>
            </div>

            <div class="col-lg-4">
                <div class="surface-card p-4 mb-3">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-briefcase me-2 text-primary"></i>Contexto do caso</h6>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-secondary text-uppercase">Área</dt>
                        <dd class="col-7">{{ $caso->area ?? '—' }}</dd>

                        <dt class="col-5 text-secondary text-uppercase">Status</dt>
                        <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $caso->status)) }}</dd>

                        @if ($caso->client_name)
                            <dt class="col-5 text-secondary text-uppercase">Cliente</dt>
                            <dd class="col-7">{{ $caso->client_name }}</dd>
                        @endif

                        <dt class="col-5 text-secondary text-uppercase">Docs</dt>
                        <dd class="col-7">{{ $caso->documents()->count() }} arquivo(s)</dd>
                    </dl>
                </div>

                <div class="surface-card p-4">
                    <h6 class="fw-semibold mb-2"><i class="bi bi-lightbulb me-2 text-warning"></i>Sugestões de perguntas</h6>
                    <ul class="list-unstyled small text-secondary mb-0">
                        <li class="mb-2">📋 Qual é o prazo processual mais urgente neste caso?</li>
                        <li class="mb-2">⚖️ Quais são os principais riscos jurídicos?</li>
                        <li class="mb-2">📄 Quais documentos ainda precisamos reunir?</li>
                        <li class="mb-2">🔍 Qual é a tese jurídica mais sólida aqui?</li>
                        <li>💡 Que estratégia você recomendaria para este caso?</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
