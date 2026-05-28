@extends('layouts.app')

@section('title', 'Configurações — Equipe')

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="settings-shell">

        @include('configuracoes._sidebar')

        <div class="settings-content">

            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Equipe</h2>
                <p class="text-secondary mb-0 small">Convide usuários, gerencie perfis de acesso e acompanhe atividades.</p>
            </div>

            <livewire:admin.configuracoes.equipe />

        </div>
    </div>
@endsection
