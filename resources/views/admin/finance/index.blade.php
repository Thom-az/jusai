@extends('layouts.admin')

@section('title', 'Financeiro')

@push('styles')
    @vite(['resources/css/modules/admin/finance.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Financeiro</h2>
                <p class="text-secondary mb-0 small">Assinaturas, faturas e receita recorrente.</p>
            </div>
            <span class="badge text-bg-warning text-dark rounded-pill px-3 py-2">
                <i class="bi bi-clock me-1"></i>Proxima fase
            </span>
        </div>

        <div class="row g-3 mb-4">
            @foreach ([['label'=>'MRR', 'w'=>'90px'], ['label'=>'ARR', 'w'=>'80px'], ['label'=>'Churn', 'w'=>'70px'], ['label'=>'LTV medio', 'w'=>'100px']] as $sk)
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card h-100">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="metric-label">{{ $sk['label'] }}</div>
                                <div class="skeleton skeleton-heading mt-1 mb-2" style="width:{{ $sk['w'] }};"></div>
                                <div class="skeleton skeleton-text" style="width:60%;"></div>
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
                            <div class="skeleton skeleton-heading mb-1" style="width:180px;"></div>
                            <div class="skeleton skeleton-text" style="width:240px;"></div>
                        </div>
                        <div class="skeleton skeleton-btn" style="width:80px;"></div>
                    </div>
                    @for ($i = 0; $i < 5; $i++)
                        <div class="list-item mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="skeleton skeleton-circle flex-shrink-0" style="width:2.2rem;height:2.2rem;"></div>
                                <div class="flex-grow-1">
                                    <div class="skeleton skeleton-text mb-1" style="width:55%;"></div>
                                    <div class="skeleton skeleton-text" style="width:38%;"></div>
                                </div>
                                <div class="skeleton skeleton-badge" style="width:64px;"></div>
                                <div class="skeleton skeleton-badge" style="width:72px;"></div>
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
                    <div class="skeleton skeleton-heading mb-3" style="width:140px;"></div>
                    @for ($i = 0; $i < 4; $i++)
                        <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom: 1px solid rgba(215,220,229,0.5);">
                            <div class="skeleton skeleton-text" style="width:45%;"></div>
                            <div class="skeleton skeleton-text" style="width:30%;"></div>
                        </div>
                    @endfor
                </div>
                <div class="surface-card p-4">
                    <div class="skeleton skeleton-heading mb-3" style="width:120px;"></div>
                    @for ($i = 0; $i < 5; $i++)
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="skeleton skeleton-badge" style="width:60px;"></div>
                            <div class="skeleton skeleton-text flex-grow-1 mb-0"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
@endsection
