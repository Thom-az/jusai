@extends('layouts.app')

@section('title', 'Configurações — Escritório')

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
             x-data="escritorioForm()"
             @input.capture="hasChanges = true"
             @change.capture="hasChanges = true"
             x-on:escritorio-saved.window="onSaved()">

            <div class="settings-section-header">
                <h2 class="mb-1">Dados do escritório</h2>
                <p class="text-secondary small">Razão social, CNPJ, endereço, logotipo e áreas de atuação.</p>
            </div>

            <livewire:admin.configuracoes.escritorio lazy />

        </div>
    </div>
@endsection
