@extends('layouts.app')

@section('title', 'Casos')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Casos</h2>
                <p class="text-secondary mb-0 small">Todos os dossies juridicos do escritorio.</p>
            </div>
            <a href="{{ route('cases.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-circle me-2"></i>Novo caso
            </a>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Listagem de Casos</h5>
            <p class="text-secondary mb-0">Filtros, busca e tabela de casos juridicos<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
