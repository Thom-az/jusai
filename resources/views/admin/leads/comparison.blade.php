@extends('layouts.admin')

@section('title', 'Comparativo de Leads')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Comparativo de Leads</h2>
                <p class="text-secondary mb-0 small">Analise por fonte, status e periodo.</p>
            </div>
            <a href="{{ route('admin.leads.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-2"></i>Voltar
            </a>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-bar-chart-line fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Comparativo por fonte e periodo</h5>
            <p class="text-secondary mb-0">Graficos de conversao, origem e evolucao mensal<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
