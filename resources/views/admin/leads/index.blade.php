@extends('layouts.admin')

@section('title', 'Leads')

@push('styles')
    @vite(['resources/css/modules/admin/leads.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Pipeline de Leads</h2>
                <p class="text-secondary mb-0 small">Acompanhamento do funil de vendas.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-warning text-dark rounded-pill px-3 py-2">
                    <i class="bi bi-clock me-1"></i>Proxima fase
                </span>
                <a href="{{ route('admin.leads.comparison') }}" class="btn btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-bar-chart me-2"></i>Comparativo
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach ([['label'=>'Total no funil'], ['label'=>'Demos agendadas'], ['label'=>'Taxa de conversao'], ['label'=>'Ticket medio']] as $sk)
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="metric-label">{{ $sk['label'] }}</div>
                                <div class="skeleton skeleton-heading mt-1 mb-2" style="width:55px;"></div>
                                <div class="skeleton skeleton-text" style="width:65%;"></div>
                            </div>
                            <div class="skeleton skeleton-circle" style="width:3rem;height:3rem;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            @foreach (['Novos Leads', 'Qualificados', 'Demo Agendada', 'Proposta Enviada'] as $col)
                <div class="col-sm-6 col-xl-3">
                    <div class="surface-card p-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="fw-semibold small">{{ $col }}</div>
                            <div class="skeleton skeleton-badge" style="width:28px;"></div>
                        </div>
                        @for ($i = 0; $i < 3; $i++)
                            <div class="list-item mb-2 p-3">
                                <div class="skeleton skeleton-text mb-1" style="width:75%;"></div>
                                <div class="skeleton skeleton-text" style="width:50%;"></div>
                                <div class="d-flex gap-2 mt-2">
                                    <div class="skeleton skeleton-badge" style="width:55px;"></div>
                                    <div class="skeleton skeleton-badge" style="width:65px;"></div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <span class="badge text-bg-secondary rounded-pill px-3 py-2 small">
                <i class="bi bi-clock me-1"></i>Kanban completo disponivel na proxima fase
            </span>
        </div>
    </div>
@endsection
