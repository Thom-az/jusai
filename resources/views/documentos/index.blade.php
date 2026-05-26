@extends('layouts.app')

@section('title', 'Documentos')

@push('styles')
    @vite(['resources/css/modules/documentos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Documentos</h2>
                <p class="text-secondary mb-0 small">PDFs e documentos analisados por IA.</p>
            </div>
            <a href="{{ route('documents.create') }}" wire:navigate class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-cloud-arrow-up me-2"></i>Enviar documento
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="surface-card p-4 mb-4">
            <form method="GET" action="{{ route('documents.index') }}" class="row g-2">
                <div class="col-sm-8 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar por nome..." value="{{ request('search') }}">
                </div>
                <div class="col-sm-4 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        @foreach (['uploading' => 'Enviando', 'processing' => 'Processando', 'ready' => 'Pronto', 'error' => 'Erro'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('documents.index') }}" wire:navigate class="btn btn-outline-secondary ms-1">Limpar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="surface-card p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Documento</th>
                            <th>Caso vinculado</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th>Enviado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $doc)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $doc->title }}</div>
                                    <div class="text-secondary small">{{ $doc->original_filename }}</div>
                                    @if ($doc->ai_summary)
                                        <div class="text-secondary small fst-italic">{{ Str::limit($doc->ai_summary, 80) }}</div>
                                    @endif
                                </td>
                                <td class="text-secondary small">
                                    @if ($doc->legalCase)
                                        <a href="{{ route('cases.show', $doc->legalCase) }}" wire:navigate class="text-decoration-none">{{ Str::limit($doc->legalCase->title, 40) }}</a>
                                    @else
                                        <span class="text-muted">Sem caso</span>
                                    @endif
                                </td>
                                <td><span class="badge text-bg-secondary">{{ $doc->mime_type }}</span></td>
                                <td>
                                    @php
                                        $statusClass = match($doc->status) {
                                            'ready'      => 'text-bg-success',
                                            'processing' => 'text-bg-warning text-dark',
                                            'error'      => 'text-bg-danger',
                                            default      => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($doc->status) }}</span>
                                </td>
                                <td class="text-secondary small">{{ $doc->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('documents.show', $doc) }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary">
                                    <i class="bi bi-file-earmark fs-2 d-block mb-2"></i>
                                    Nenhum documento encontrado.
                                    <a href="{{ route('documents.create') }}" wire:navigate class="d-block mt-1">Enviar primeiro documento</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($documents->hasPages())
                <div class="p-4">{{ $documents->links() }}</div>
            @endif
        </div>
    </div>
@endsection
