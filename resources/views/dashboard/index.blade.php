@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
    @vite(['resources/css/modules/dashboard.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <section class="hero-card p-4 p-lg-5 mb-4">
            <div class="row align-items-center g-4">
                <div class="col-xl-8">
                    <span class="hero-chip mb-3">
                        <i class="bi bi-stars"></i>
                        Copiloto jurídico com suporte de IA
                    </span>
                    <h1 class="display-6 fw-semibold mb-3">Operação jurídica organizada, rastreável e com IA integrada.</h1>
                    <p class="fs-5 text-white-50 mb-4">
                        Gerencie casos, documentos e minutas com análise de IA. Todo resultado deve ser validado por profissional habilitado.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('cases.create') }}" wire:navigate class="btn btn-light rounded-pill px-4">
                            <i class="bi bi-folder-plus me-2"></i>Criar caso
                        </a>
                        <a href="{{ route('documents.create') }}" wire:navigate class="btn btn-outline-light rounded-pill px-4">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Enviar documento
                        </a>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="glass-card p-4 bg-white bg-opacity-10 border border-white border-opacity-10 text-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="small text-uppercase text-white-50 fw-semibold">Compliance IA</span>
                            <span class="badge rounded-pill text-bg-success">Ativo</span>
                        </div>
                        <p class="mb-3 text-white-50">
                            {{ config('jusai.ai.review_notice') }}
                        </p>
                        <div class="small text-white-50">Provider</div>
                        <div class="fw-semibold">{{ strtoupper(config('jusai.ai.provider')) }} / {{ config('jusai.ai.model_strong') }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-3 g-lg-4 mb-4">
            @foreach ($metrics as $metric)
                <div class="col-sm-6 col-xxl-3">
                    <article class="stat-card h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="text-secondary small text-uppercase fw-semibold mb-2">{{ $metric['label'] }}</div>
                                <div class="display-6 fw-semibold text-dark mb-1">{{ $metric['value'] }}</div>
                                <div class="small text-secondary">{{ $metric['trend'] }}</div>
                            </div>
                            <div class="stat-icon {{ $metric['icon_class'] }}">
                                <i class="bi {{ $metric['icon'] }}"></i>
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </section>

        <section class="row g-4">
            <div class="col-xl-8">
                <div class="surface-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
                        <div>
                            <h2 class="section-title mb-1">Casos recentes</h2>
                            <p class="section-subtitle mb-0">Dossiês mais movimentados no momento.</p>
                        </div>
                        <a href="{{ route('cases.index') }}" wire:navigate class="btn btn-outline-primary rounded-pill">
                            <i class="bi bi-arrow-right me-2"></i>Ver todos
                        </a>
                    </div>

                    <div class="d-grid gap-3">
                        @forelse ($recentCases as $case)
                            <article class="list-item">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <a href="{{ route('cases.show', $case) }}" wire:navigate class="h5 mb-1 text-decoration-none text-dark fw-semibold d-block">{{ $case->title }}</a>
                                        <div class="text-secondary mb-2">{{ $case->client_name }} &bull; {{ ucfirst($case->area) }}</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="status-badge status-active">
                                                <i class="bi bi-record-circle"></i>{{ ucfirst(str_replace('_', ' ', $case->status)) }}
                                            </span>
                                            @if ($case->risk_level)
                                                <span class="status-badge status-neutral">
                                                    <i class="bi bi-shield-exclamation"></i>Risco {{ ucfirst($case->risk_level) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-secondary small">{{ $case->updated_at->diffForHumans() }}</div>
                                </div>
                            </article>
                        @empty
                            <p class="text-secondary">Nenhum caso cadastrado ainda. <a href="{{ route('cases.create') }}" wire:navigate>Criar primeiro caso.</a></p>
                        @endforelse
                    </div>
                </div>

                <div class="surface-card p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
                        <div>
                            <h2 class="section-title mb-1">Atividades recentes</h2>
                            <p class="section-subtitle mb-0">Auditoria dos eventos mais recentes da operação.</p>
                        </div>
                    </div>

                    <div class="d-grid gap-4">
                        @forelse ($activities as $activity)
                            <article class="timeline-item">
                                <span class="timeline-dot"></span>
                                <div class="fw-semibold mb-1">{{ $activity->event }}</div>
                                <p class="text-secondary mb-1">{{ $activity->description }}</p>
                                <div class="small text-secondary">{{ $activity->created_at->diffForHumans() }}</div>
                            </article>
                        @empty
                            <p class="text-secondary small">Nenhuma atividade registrada ainda.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="surface-card p-4 mb-4">
                    <div class="mb-4">
                        <h2 class="section-title mb-1">Atalhos rápidos</h2>
                        <p class="section-subtitle mb-0">Entradas principais para os fluxos.</p>
                    </div>

                    <div class="d-grid gap-3">
                        @foreach ($quickActions as $action)
                            <a href="{{ $action['route'] }}" wire:navigate class="quick-action">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="stat-icon icon-blue flex-shrink-0">
                                        <i class="bi {{ $action['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">{{ $action['title'] }}</div>
                                        <div class="small text-secondary">{{ $action['description'] }}</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="surface-card p-4 mb-4 review-banner">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-exclamation-diamond fs-3"></i>
                        <div>
                            <div class="fw-semibold mb-2">Revisão jurídica obrigatória</div>
                            <p class="mb-0 small">{{ config('jusai.ai.review_notice') }}</p>
                        </div>
                    </div>
                </div>

                <div class="surface-card p-4">
                    <div class="mb-3">
                        <h2 class="section-title mb-1">Avisos</h2>
                    </div>
                    <div class="d-grid gap-3">
                        @foreach ($alerts as $alert)
                            <div class="list-item">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="bi bi-info-circle text-primary mt-1"></i>
                                    <div class="small">{{ $alert }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
