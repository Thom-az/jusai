@extends('layouts.app')

@section('title', 'Casos')

@push('styles')
    @vite(['resources/css/modules/casos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Casos</h2>
                <p class="text-secondary mb-0 small">Dossies juridicos do escritorio.</p>
            </div>
            <a href="{{ route('cases.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-folder-plus me-2"></i>Novo caso
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="surface-card p-4 mb-4">
            <form method="GET" action="{{ route('cases.index') }}" class="row g-2">
                <div class="col-sm-6 col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Buscar caso ou cliente..." value="{{ request('search') }}">
                </div>
                <div class="col-sm-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Todos os status</option>
                        @foreach (['triagem' => 'Triagem', 'em_andamento' => 'Em andamento', 'aguardando_cliente' => 'Aguardando cliente', 'aguardando_prazo' => 'Aguardando prazo', 'em_recurso' => 'Em recurso', 'encerrado' => 'Encerrado', 'arquivado' => 'Arquivado'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <select name="area" class="form-select">
                        <option value="">Todas as areas</option>
                        @foreach (['civil' => 'Civil', 'criminal' => 'Criminal', 'trabalhista' => 'Trabalhista', 'tributario' => 'Tributario', 'empresarial' => 'Empresarial', 'familia' => 'Familia', 'imobiliario' => 'Imobiliario', 'previdenciario' => 'Previdenciario', 'administrativo' => 'Administrativo', 'outro' => 'Outro'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('area') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-md-2">
                    <select name="risk_level" class="form-select">
                        <option value="">Todos os riscos</option>
                        @foreach (['baixo' => 'Baixo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Critico'] as $val => $label)
                            <option value="{{ $val }}" @selected(request('risk_level') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                    @if (request()->hasAny(['search', 'status', 'area', 'risk_level']))
                        <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary ms-1">Limpar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="surface-card p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Caso</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Risco</th>
                            <th>Responsavel</th>
                            <th>Atualizado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cases as $case)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $case->title }}</div>
                                    <div class="text-secondary small">{{ $case->client_name }}</div>
                                </td>
                                <td><span class="badge text-bg-secondary">{{ ucfirst($case->area) }}</span></td>
                                <td>
                                    <span class="badge text-bg-primary">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span>
                                </td>
                                <td>
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
                                        <span class="badge {{ $riskClass }}">{{ ucfirst($case->risk_level) }}</span>
                                    @else
                                        <span class="text-secondary small">—</span>
                                    @endif
                                </td>
                                <td class="text-secondary small">{{ $case->assignedUser?->name ?? '—' }}</td>
                                <td class="text-secondary small">{{ $case->updated_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('cases.show', $case) }}" class="btn btn-sm btn-outline-primary rounded-pill">Visualizar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-secondary">
                                    <i class="bi bi-briefcase fs-2 d-block mb-2"></i>
                                    Nenhum caso encontrado.
                                    <a href="{{ route('cases.create') }}" class="d-block mt-2">Criar primeiro caso</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($cases->hasPages())
                <div class="p-4">{{ $cases->links() }}</div>
            @endif
        </div>
    </div>
@endsection
