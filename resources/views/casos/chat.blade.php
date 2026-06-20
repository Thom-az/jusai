@extends('layouts.app')

@section('title', 'Assistente Jurídico — ' . $caso->title)

@section('content')
    <div class="container-fluid px-0 d-flex flex-column chat-page-wrap">

        {{-- Header compacto --}}
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
            <a href="{{ route('cases.show', $caso) }}" wire:navigate
               class="btn btn-outline-secondary btn-sm rounded-pill px-3 flex-shrink-0">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1 min-width-0">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h5 class="fw-semibold mb-0 text-truncate">{{ $caso->title }}</h5>
                    @if ($caso->area)
                        <span class="badge text-bg-secondary">{{ $caso->area }}</span>
                    @endif
                    <span class="badge {{ in_array($caso->status, ['ativo', 'active']) ? 'text-bg-success' : 'text-bg-secondary' }}">
                        {{ ucfirst(str_replace('_', ' ', $caso->status)) }}
                    </span>
                    @if ($caso->client_name)
                        <span class="text-secondary small d-none d-md-inline">
                            <i class="bi bi-person me-1"></i>{{ $caso->client_name }}
                        </span>
                    @endif
                </div>
            </div>
            <span class="badge text-bg-primary flex-shrink-0">
                <i class="bi bi-cpu me-1"></i>Assistente IA
            </span>
        </div>

        {{-- Chat ocupa o restante da altura --}}
        <div class="surface-card p-0 overflow-hidden flex-grow-1">
            <livewire:caso-chat :caso="$caso" />
        </div>

    </div>
@endsection
