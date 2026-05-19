@extends('layouts.app')

@section('title', 'Detalhe do Caso')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-briefcase fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Detalhe do Caso</h5>
            <p class="text-secondary mb-0">Documentos, ai_reviews e timeline do caso<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
