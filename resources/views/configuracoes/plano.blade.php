@extends('layouts.app')

@section('title', 'Configurações — Plano')

@push('styles')
    @vite(['resources/css/modules/configuracoes.css'])
@endpush

@section('content')
    <div class="settings-shell">

        <x-settings-sidebar :current="$current" />

        <div class="settings-content">

            <div class="settings-section-header">
                <h2 class="fw-semibold mb-1" style="font-size: 1.2rem;">Plano e faturamento</h2>
                <p class="text-secondary mb-0 small">Plano atual, uso do mês, histórico de faturas e método de pagamento.</p>
            </div>

            <livewire:admin.configuracoes.plano lazy />

        </div>
    </div>
@endsection
