@extends('layouts.app')

@section('title', 'Configurações — Preferências')

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
             x-data="preferenciasForm()"
             x-on:apply-theme-preference.window="applyTheme($event.detail.theme)"
             x-on:preferencias-saved.window="$dispatch('app:toast', { message: 'Preferências salvas com sucesso.', type: 'success' })">

            <div class="settings-section-header">
                <h2 class="mb-1">Preferências</h2>
                <p class="text-secondary small">Tema, idioma, fuso horário, densidade da interface e notificações.</p>
            </div>

            <livewire:admin.configuracoes.preferencias lazy />

        </div>
    </div>
@endsection
