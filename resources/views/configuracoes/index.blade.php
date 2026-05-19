@extends('layouts.app')

@section('title', 'Configuracoes')

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Configuracoes</h2>
                <p class="text-secondary mb-0 small">Dados do escritorio, usuarios e preferencias.</p>
            </div>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-sliders fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Configuracoes do Escritorio</h5>
            <p class="text-secondary mb-0">Edicao de perfil, gestao de usuarios e plano<br>serao implementados na proxima fase.</p>
        </div>
    </div>
@endsection
