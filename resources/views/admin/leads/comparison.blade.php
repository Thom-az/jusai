@extends('layouts.admin')

@section('title', 'Comparativo de Leads')

@push('styles')
    @vite(['resources/css/modules/admin/leads.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Comparativo de Leads</h2>
                <p class="text-secondary mb-0 small">Analise por fonte, status e periodo.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-warning text-dark rounded-pill px-3 py-2">
                    <i class="bi bi-clock me-1"></i>Proxima fase
                </span>
                <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-2"></i>Voltar
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="surface-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="skeleton skeleton-heading" style="width:200px;"></div>
                        <div class="skeleton skeleton-btn" style="width:120px;"></div>
                    </div>
                    <div class="skeleton" style="height:240px;border-radius:1rem;"></div>
                    <div class="d-flex justify-content-center gap-4 mt-3">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="d-flex align-items-center gap-2">
                                <div class="skeleton skeleton-circle" style="width:10px;height:10px;"></div>
                                <div class="skeleton skeleton-text mb-0" style="width:60px;"></div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="surface-card p-4 mb-4">
                    <div class="skeleton skeleton-heading mb-3" style="width:130px;"></div>
                    <div class="skeleton" style="height:160px;border-radius:1rem;"></div>
                </div>
                <div class="surface-card p-4">
                    <div class="skeleton skeleton-heading mb-3" style="width:140px;"></div>
                    @for ($i = 0; $i < 4; $i++)
                        <div class="d-flex align-items-center justify-content-between py-2" style="border-bottom: 1px solid rgba(215,220,229,0.5);">
                            <div class="skeleton skeleton-text" style="width:40%;"></div>
                            <div class="skeleton skeleton-text" style="width:25%;"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="text-center">
            <span class="badge text-bg-secondary rounded-pill px-3 py-2 small">
                <i class="bi bi-clock me-1"></i>Graficos completos disponíveis na proxima fase
            </span>
        </div>
    </div>
@endsection
