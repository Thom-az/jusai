@extends('layouts.app')

@section('title', 'Minutas')

@push('styles')
    @vite(['resources/css/modules/minutas.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">
                    Minutas
                    <span class="badge ms-2 align-middle"
                          style="font-size:0.62rem;padding:0.22rem 0.65rem;border-radius:999px;background:rgba(37,99,235,0.1);color:var(--jusai-action);font-weight:600;vertical-align:middle;letter-spacing:0.02em;">
                        Em breve
                    </span>
                </h2>
                <p class="text-secondary mb-0 small">Rascunhos jurídicos gerados e revisados com IA.</p>
            </div>
        </div>

        <div class="surface-card p-5">
            <x-empty-state
                icon="bi-journal-richtext"
                title="Gerador de minutas chegando em breve"
                description="Esta seção reunirá criação de rascunhos assistida por IA, versionamento e um fluxo de revisão completo com histórico. Você será notificado assim que estiver disponível."
                size="lg"
            />

            <div class="mx-auto mt-5" style="max-width: 460px;">
                <x-ai-disclaimer variant="banner" />
            </div>
        </div>
    </div>
@endsection
