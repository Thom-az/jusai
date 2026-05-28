@extends('layouts.app')

@section('title', 'Configurações — Preferências')

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="settings-shell">

        @include('configuracoes._sidebar')

        <div class="settings-content">

            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Preferências</h2>
                <p class="text-secondary mb-0 small">Tema, idioma, fuso horário, densidade da interface e notificações.</p>
            </div>

            <livewire:admin.configuracoes.preferencias />

        </div>
    </div>
@endsection
