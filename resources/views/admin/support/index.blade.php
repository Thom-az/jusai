@extends('layouts.admin')

@section('title', 'Chamados')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-semibold mb-1">Chamados de Suporte</h2>
                <p class="text-secondary mb-0 small">Gerenciamento de tickets e SLA.</p>
            </div>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-headset fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Modulo de Chamados</h5>
            <p class="text-secondary mb-0">Listagem de tickets, prioridade, SLA e respostas internas<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
