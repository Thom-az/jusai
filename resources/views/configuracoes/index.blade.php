@extends('layouts.app')

@section('title', 'Configurações')

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Configurações</h2>
                <p class="text-secondary mb-0 small">Dados do escritório, usuários e preferências.</p>
            </div>
        </div>

        <div class="placeholder-hero d-flex flex-column align-items-center justify-content-center text-center p-5">
            <i class="bi bi-sliders fs-1 text-secondary mb-3"></i>
            <h5 class="fw-semibold mb-2">Configurações do Escritório</h5>
            <p class="text-secondary mb-0">Edição de perfil, gestão de usuários e plano<br>serão implementados na próxima fase.</p>
        </div>
    </div>
@endsection
