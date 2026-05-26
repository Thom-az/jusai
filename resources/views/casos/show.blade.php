@extends('layouts.app')

@section('title', $case->title)

@push('styles')
    @vite(['resources/css/modules/casos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="{{ route('cases.index') }}" wire:navigate class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1">
                <h2 class="fw-semibold mb-1">{{ $case->title }}</h2>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge text-bg-primary">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span>
                    <span class="badge text-bg-secondary">{{ ucfirst($case->area) }}</span>
                    @if ($case->risk_level)
                        @php
                            $riskClass = match($case->risk_level) {
                                'baixo'   => 'text-bg-success',
                                'medio'   => 'text-bg-warning text-dark',
                                'alto'    => 'text-bg-danger',
                                'critico' => 'text-bg-dark',
                                default   => 'text-bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $riskClass }}">Risco {{ ucfirst($case->risk_level) }}</span>
                    @endif
                    <span class="text-secondary small">Cliente: {{ $case->client_name }}</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('cases.edit', $case) }}" wire:navigate class="btn btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-pencil me-1"></i>Editar
                </a>
                <form method="POST" action="{{ route('cases.destroy', $case) }}" onsubmit="return confirm('Excluir este caso permanentemente?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rounded-pill px-3">
                        <i class="bi bi-trash me-1"></i>Excluir
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <ul class="nav nav-tabs mb-4" id="caseTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#documentos">
                    <i class="bi bi-file-earmark-text me-1"></i>Documentos
                    <span class="badge text-bg-secondary ms-1">{{ $case->documents->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#analises">
                    <i class="bi bi-cpu me-1"></i>Analises de IA
                    <span class="badge text-bg-secondary ms-1">{{ $case->aiReviews->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#detalhes">
                    <i class="bi bi-info-circle me-1"></i>Detalhes
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="documentos">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold mb-0">Documentos do caso</h5>
                    <a href="{{ route('documents.create', ['case_id' => $case->id]) }}" wire:navigate class="btn btn-primary rounded-pill">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Enviar documento
                    </a>
                </div>
                @forelse ($case->documents as $doc)
                    <div class="surface-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <a href="{{ route('documents.show', $doc) }}" wire:navigate class="fw-semibold text-decoration-none">{{ $doc->title }}</a>
                                <div class="text-secondary small">{{ $doc->original_filename }} &bull; {{ number_format($doc->file_size / 1024, 0) }} KB</div>
                                @if ($doc->ai_summary)
                                    <p class="text-secondary small mt-1 mb-0">{{ Str::limit($doc->ai_summary, 120) }}</p>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $statusClass = match($doc->status) {
                                        'ready'      => 'text-bg-success',
                                        'processing' => 'text-bg-warning text-dark',
                                        'error'      => 'text-bg-danger',
                                        default      => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($doc->status) }}</span>
                                <a href="{{ route('documents.show', $doc) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="surface-card p-4 text-center text-secondary">
                        <i class="bi bi-file-earmark fs-2 d-block mb-2"></i>
                        Nenhum documento enviado.
                        <a href="{{ route('documents.create', ['case_id' => $case->id]) }}" wire:navigate class="d-block mt-1">Enviar primeiro documento</a>
                    </div>
                @endforelse
            </div>

            <div class="tab-pane fade" id="analises">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-semibold mb-0">Analises de IA</h5>
                    <a href="{{ route('review.index') }}?case_id={{ $case->id }}" wire:navigate class="btn btn-primary rounded-pill">
                        <i class="bi bi-cpu me-2"></i>Nova analise
                    </a>
                </div>
                @forelse ($case->aiReviews->sortByDesc('created_at') as $review)
                    <div class="surface-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $review->type)) }}</div>
                                <div class="text-secondary small">
                                    Por {{ $review->creator?->name ?? 'sistema' }} &bull; {{ $review->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $reviewStatus = match($review->status) {
                                        'concluido'   => 'text-bg-success',
                                        'processando' => 'text-bg-warning text-dark',
                                        'erro'        => 'text-bg-danger',
                                        default       => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $reviewStatus }}">{{ ucfirst($review->status) }}</span>
                                @if ($review->reviewed_at)
                                    <span class="badge text-bg-info">Revisado</span>
                                @endif
                                <a href="{{ route('review.show', $review) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="surface-card p-4 text-center text-secondary">
                        <i class="bi bi-cpu fs-2 d-block mb-2"></i>
                        Nenhuma analise de IA realizada ainda.
                    </div>
                @endforelse
            </div>

            <div class="tab-pane fade" id="detalhes">
                <div class="surface-card p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Cliente</div>
                            <div>{{ $case->client_name }}</div>
                        </div>
                        @if ($case->client_email)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">E-mail</div>
                                <div>{{ $case->client_email }}</div>
                            </div>
                        @endif
                        @if ($case->client_phone)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">Telefone</div>
                                <div>{{ $case->client_phone }}</div>
                            </div>
                        @endif
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Responsavel</div>
                            <div>{{ $case->assignedUser?->name ?? 'Nao atribuido' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Criado por</div>
                            <div>{{ $case->creator?->name ?? '—' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Abertura</div>
                            <div>{{ $case->opened_at?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                        @if ($case->description)
                            <div class="col-12">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">Descricao</div>
                                <div style="white-space: pre-wrap;">{{ $case->description }}</div>
                            </div>
                        @endif
                        @if ($case->internal_notes)
                            <div class="col-12">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">Notas internas</div>
                                <div class="p-3 bg-warning bg-opacity-10 rounded" style="white-space: pre-wrap;">{{ $case->internal_notes }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/modules/casos-show.js'])
@endpush
