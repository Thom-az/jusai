@extends('layouts.admin')

@section('title', 'Chamados')

@push('styles')
    @vite(['resources/css/modules/admin/support.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Chamados de Suporte</h2>
                <p class="text-secondary mb-0 small">Gerenciamento de tickets e SLA.</p>
            </div>
            <span class="badge text-bg-warning text-dark rounded-pill px-3 py-2">
                <i class="bi bi-clock me-1"></i>Proxima fase
            </span>
        </div>

        <div class="row g-3 mb-4">
            @foreach ([['label'=>'Abertos', 'icon_class'=>'icon-red'], ['label'=>'Em andamento', 'icon_class'=>'icon-gold'], ['label'=>'Resolvidos hoje', 'icon_class'=>'icon-green'], ['label'=>'SLA em risco', 'icon_class'=>'icon-red']] as $sk)
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="metric-label">{{ $sk['label'] }}</div>
                                <div class="skeleton skeleton-heading mt-1 mb-2" style="width:50px;"></div>
                                <div class="skeleton skeleton-text" style="width:70%;"></div>
                            </div>
                            <div class="skeleton skeleton-circle" style="width:3rem;height:3rem;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="surface-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <div class="skeleton skeleton-heading mb-1" style="width:160px;"></div>
                            <div class="skeleton skeleton-text" style="width:220px;"></div>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="skeleton skeleton-btn" style="width:80px;"></div>
                            <div class="skeleton skeleton-btn" style="width:80px;"></div>
                        </div>
                    </div>
                    @for ($i = 0; $i < 6; $i++)
                        <div class="list-item mb-3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="skeleton skeleton-badge flex-shrink-0 mt-1" style="width:56px;"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-text mb-1" style="width:65%;"></div>
                                    <div class="skeleton skeleton-text" style="width:42%;"></div>
                                </div>
                                <div class="skeleton skeleton-badge" style="width:70px;"></div>
                            </div>
                        </div>
                    @endfor
                    <div class="text-center pt-2">
                        <span class="badge text-bg-secondary rounded-pill px-3 py-2 small">Disponivel na proxima fase</span>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="surface-card p-4 mb-4">
                    <div class="skeleton skeleton-heading mb-3" style="width:120px;"></div>
                    @foreach ([['w'=>'70%'], ['w'=>'55%'], ['w'=>'45%'], ['w'=>'35%']] as $bar)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <div class="skeleton skeleton-text" style="width:80px;"></div>
                                <div class="skeleton skeleton-text" style="width:30px;"></div>
                            </div>
                            <div class="skeleton" style="height:8px;border-radius:999px;width:{{ $bar['w'] }};"></div>
                        </div>
                    @endforeach
                </div>
                <div class="surface-card p-4">
                    <div class="skeleton skeleton-heading mb-3" style="width:100px;"></div>
                    @for ($i = 0; $i < 4; $i++)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="skeleton skeleton-circle flex-shrink-0" style="width:1.8rem;height:1.8rem;"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton skeleton-text mb-0"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
@endsection
