@extends('layouts.admin')

@section('title', 'Nova Organização')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div>
                <h2 class="fw-semibold mb-1">Nova Organização</h2>
                <p class="text-secondary mb-0 small">Cadastrar novo escritório na plataforma.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="surface-card p-4">
                    <div class="d-flex align-items-center gap-3 p-4 bg-warning bg-opacity-10 rounded mb-4">
                        <i class="bi bi-tools text-warning fs-4"></i>
                        <div>
                            <div class="fw-semibold">Funcionalidade em desenvolvimento</div>
                            <div class="text-secondary small">O cadastro de organizações via painel admin será implementado na próxima fase.</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-1"></i>Voltar à lista
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
