@extends('layouts.app')

@section('title', $moduleTitle)

@section('content')
    <div class="container-fluid px-0">
        <section class="surface-card placeholder-hero p-4 p-lg-5">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <div class="text-uppercase small fw-semibold text-primary mb-2">{{ $moduleEyebrow }}</div>
                    <h1 class="display-6 fw-semibold text-dark mb-3">{{ $moduleTitle }}</h1>
                    <p class="lead text-secondary mb-4">
                        {{ $moduleDescription }}
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i>Voltar ao dashboard
                        </a>
                        <button class="btn btn-outline-secondary rounded-pill px-4" type="button" data-disabled-action="{{ $moduleAction }}">
                            <i class="bi bi-hammer me-2"></i>Proxima entrega
                        </button>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="surface-card p-4 h-100">
                        <div class="stat-icon icon-gold mb-3">
                            <i class="bi {{ $moduleIcon }}"></i>
                        </div>
                        <div class="fw-semibold mb-2">Etapa prevista</div>
                        <p class="text-secondary mb-0 small">{{ $moduleAction }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
