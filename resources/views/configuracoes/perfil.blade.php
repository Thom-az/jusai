@extends('layouts.app')

@section('title', 'Configurações — Perfil')

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="settings-shell">

        <x-settings-sidebar :current="$current" />

        <div class="settings-content">

            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Perfil</h2>
                <p class="text-secondary mb-0 small">Suas informações pessoais, foto, número da OAB e senha.</p>
            </div>

            <livewire:admin.configuracoes.perfil lazy />

        </div>
    </div>
@endsection
