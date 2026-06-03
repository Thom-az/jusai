@extends('layouts.app')

@section('title', 'Configurações — Segurança')

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
             x-data="segurancaForm()"
             x-on:show-security-toast.window="showToast($event.detail.message, $event.detail.type)">

            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Segurança</h2>
                <p class="text-secondary mb-0 small">Autenticação em dois fatores, sessões ativas, política de senha e conformidade LGPD.</p>
            </div>

            <livewire:admin.configuracoes.seguranca lazy />

        </div>
    </div>
@endsection
