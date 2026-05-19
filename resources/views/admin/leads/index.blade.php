@extends('layouts.admin')

@section('title', 'Leads')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Pipeline de Leads</h2>
                <p class="text-secondary mb-0 small">Acompanhamento do funil de vendas.</p>
            </div>
            <a href="{{ route('admin.leads.comparison') }}" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-bar-chart me-2"></i>Comparativo
            </a>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-person-lines-fill fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Pipeline de Vendas</h5>
            <p class="text-secondary mb-0">Kanban de leads, historico de interacoes e conversao<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
