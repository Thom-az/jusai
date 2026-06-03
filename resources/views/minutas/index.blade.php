@extends('layouts.app')

@section('title', 'Minutas')

@push('styles')
    @vite(['resources/css/modules/minutas.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Minutas</h2>
                <p class="text-secondary mb-0 small">Rascunhos jurídicos gerados com IA.</p>
            </div>
            <a href="{{ route('drafts.create') }}" wire:navigate class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-journal-plus me-2"></i>Nova minuta
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($drafts->isEmpty())
            <div class="surface-card p-5">
                <x-empty-state
                    icon="bi-journal-richtext"
                    title="Nenhuma minuta ainda"
                    description="Crie sua primeira minuta jurídica gerada por IA. Descreva o documento que precisa e a IA irá redigir um rascunho completo para revisão."
                    size="lg"
                />
                <div class="text-center mt-4">
                    <a href="{{ route('drafts.create') }}" wire:navigate class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-journal-plus me-2"></i>Criar primeira minuta
                    </a>
                </div>
            </div>
        @else
            <div class="surface-card p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Título</th>
                                <th>Tipo</th>
                                <th>Caso</th>
                                <th>Status</th>
                                <th>Criado por</th>
                                <th>Data</th>
                                <th class="pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($drafts as $draft)
                                @php
                                    $isGenerating = $draft->generated_by_ai && $draft->content === '';
                                    $statusClass = match($draft->status) {
                                        'aprovado'   => 'text-bg-success',
                                        'publicado'  => 'text-bg-primary',
                                        'em_revisao' => 'text-bg-warning text-dark',
                                        'rejeitado'  => 'text-bg-danger',
                                        default      => 'text-bg-secondary',
                                    };
                                    $typeLabel = match($draft->type) {
                                        'peticao_inicial'           => 'Petição Inicial',
                                        'contestacao'               => 'Contestação',
                                        'recurso'                   => 'Recurso',
                                        'notificacao_extrajudicial' => 'Notificação',
                                        'contrato'                  => 'Contrato',
                                        'parecer'                   => 'Parecer',
                                        default                     => 'Outros',
                                    };
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('drafts.show', $draft) }}" wire:navigate class="fw-medium text-decoration-none">
                                            {{ $draft->title }}
                                        </a>
                                        @if ($isGenerating)
                                            <span class="ms-2 badge text-bg-warning text-dark small">
                                                <span class="spinner-border spinner-border-sm me-1" style="width:.65rem;height:.65rem;"></span>Gerando…
                                            </span>
                                        @endif
                                        @if ($draft->generated_by_ai)
                                            <i class="bi bi-cpu ms-1 text-primary small" title="Gerado por IA"></i>
                                        @endif
                                    </td>
                                    <td><span class="badge text-bg-light border text-dark small">{{ $typeLabel }}</span></td>
                                    <td class="small text-secondary">
                                        @if ($draft->legalCase)
                                            <a href="{{ route('cases.show', $draft->legalCase) }}" wire:navigate class="text-decoration-none text-secondary">
                                                {{ Str::limit($draft->legalCase->title, 30) }}
                                            </a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $draft->status)) }}</span></td>
                                    <td class="small text-secondary">{{ $draft->creator?->name ?? '—' }}</td>
                                    <td class="small text-secondary">{{ $draft->updated_at->format('d/m/Y') }}</td>
                                    <td class="pe-4">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="{{ route('drafts.show', $draft) }}" wire:navigate class="btn btn-sm btn-outline-secondary rounded-pill px-3">Ver</a>
                                            @if (!$isGenerating)
                                                <a href="{{ route('drafts.edit', $draft) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill px-3">Editar</a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($drafts->hasPages())
                    <div class="px-4 py-3 border-top">
                        {{ $drafts->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
