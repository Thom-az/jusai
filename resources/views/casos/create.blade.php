@extends('layouts.app')

@section('title', 'Novo Caso')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div>
                <h2 class="fw-semibold mb-0">Novo Caso</h2>
            </div>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-folder-plus fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Formulario de Caso</h5>
            <p class="text-secondary mb-0">Formulario de cadastro de caso com todos os campos<br>sera implementado na proxima fase.</p>
        </div>
    </div>
@endsection
