@extends('layouts.app')

@section('title', 'Configurações — Perfil')

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="settings-shell">

        {{-- Sidebar secundária de navegação --}}
        @include('configuracoes._sidebar')

        {{-- Área de conteúdo --}}
        <div class="settings-content">

            {{-- Cabeçalho da seção --}}
            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Perfil</h2>
                <p class="text-secondary mb-0 small">Suas informações pessoais, foto, número da OAB e senha.</p>
            </div>

            {{-- Componente Livewire da seção --}}
            <livewire:admin.configuracoes.perfil />

        </div>
    </div>
@endsection
