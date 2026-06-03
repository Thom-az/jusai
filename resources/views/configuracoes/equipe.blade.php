@extends('layouts.app')

@section('title', 'Configurações — Equipe')

@push('scripts')
    @vite(['resources/js/modules/configuracoes-alpine.js'])
@endpush

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="settings-shell">

        <x-settings-sidebar :current="$current" />

        <div class="settings-content"
             x-data="equipeForm()"
             x-on:open-invite-modal.window="showInviteModal()"
             x-on:close-user-drawer.window="$dispatch('close-drawer', { id: 'drawerUsuario' })"
             x-on:show-team-toast.window="showToast($event.detail.message, $event.detail.type)">

            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Equipe</h2>
                <p class="text-secondary mb-0 small">Convide usuários, gerencie perfis de acesso e acompanhe atividades.</p>
            </div>

            <livewire:admin.configuracoes.equipe lazy />

        </div>
    </div>
@endsection
