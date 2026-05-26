@extends('layouts.app')

@section('title', 'Revisor Jurídico')

@push('styles')
    @vite(['resources/css/modules/revisor.css'])
@endpush

@section('content')
<div class="container-fluid px-0"
     x-data="{ type: '{{ old('type', '') }}' }">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-semibold mb-1">Revisor Jurídico</h2>
            <p class="text-secondary mb-0 small">Análise de documentos e peças com suporte de IA.</p>
        </div>
    </div>

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($reviews->isEmpty())

    {{-- Layout centrado — sem análises --}}
    <div class="d-flex flex-column align-items-center justify-content-center py-4">
        <div class="revisor-solo-wrap">
            <div class="text-center mb-4">
                <div class="revisor-solo-icon mb-3">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h5 class="fw-semibold mb-1">Nova análise de IA</h5>
                <p class="text-secondary small mb-0">Selecione o tipo de análise e o caso para começar.</p>
            </div>

            <div class="surface-card p-4">
                @include('revisor._form', ['compact' => false])
            </div>
        </div>
    </div>

    @else

    {{-- Layout 2 colunas — com análises --}}
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="surface-card p-4">
                <h5 class="fw-semibold mb-4">Nova análise de IA</h5>
                @include('revisor._form', ['compact' => true])
            </div>
        </div>

        <div class="col-lg-7">
            <div class="surface-card p-4">
                <h5 class="fw-semibold mb-4">Análises recentes</h5>
                @forelse ($reviews as $review)
                    <div class="list-item mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</div>
                                <div class="text-secondary small">
                                    {{ \Str::limit($review->legalCase?->title ?? '—', 50) }}
                                    &bull; {{ $review->created_at->diffForHumans() }}
                                </div>
                                @if ($review->document)
                                    <div class="text-secondary small">Doc: {{ $review->document->title }}</div>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                @php
                                    $statusClass = match($review->status) {
                                        'concluido'   => 'text-bg-success',
                                        'processando' => 'text-bg-warning text-dark',
                                        'erro'        => 'text-bg-danger',
                                        default       => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($review->status) }}</span>
                                @if ($review->reviewed_at)
                                    <span class="badge text-bg-info">Revisado</span>
                                @endif
                                <a href="{{ route('review.show', $review) }}" wire:navigate
                                   class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <x-empty-state
                        icon="bi-cpu"
                        title="Nenhuma análise ainda"
                        description="As análises realizadas aparecem aqui com status e resultado."
                        size="sm"
                        :muted="true"
                    />
                @endforelse
            </div>
        </div>
    </div>

    @endif

</div>
@endsection
