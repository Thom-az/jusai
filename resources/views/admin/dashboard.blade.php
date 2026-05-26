@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
    @vite(['resources/css/modules/admin/dashboard.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/admin/dashboard.js'])
@endpush

@section('content')
    <div class="container-fluid px-0">

        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Painel Administrativo</h2>
                <p class="text-secondary mb-0 small">Visão geral de MRR, suporte e pipeline de vendas.</p>
            </div>
            <span class="badge text-bg-danger px-3 py-2 rounded-pill fs-6">
                <i class="bi bi-shield-lock-fill me-1"></i>Acesso restrito
            </span>
        </div>

        <div class="row g-3 mb-4">
            @foreach ($metrics as $m)
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card h-100">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="flex-grow-1 min-width-0">
                                <div class="metric-label">{{ $m['label'] }}</div>
                                <div class="metric-value">{{ $m['value'] }}</div>
                                <div class="metric-trend">{{ $m['trend'] }}</div>
                            </div>
                            <div class="metric-icon {{ $m['icon_class'] }}">
                                <i class="bi {{ $m['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="surface-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="fw-semibold mb-1">Organizações recentes</h5>
                            <p class="text-secondary small mb-0">Últimos escritórios cadastrados.</p>
                        </div>
                        <a href="{{ route('admin.organizations.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver todas</a>
                    </div>

                    @forelse ($recentOrganizations as $org)
                        <div class="list-item mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="stat-icon icon-blue flex-shrink-0" style="width:2.4rem;height:2.4rem;border-radius:0.75rem;font-size:0.95rem;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold small">{{ $org->name }}</div>
                                        <div class="text-secondary" style="font-size:0.78rem;">{{ $org->email }}</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill @if($org->status === 'active') text-bg-success @elseif($org->status === 'trial') text-bg-warning text-dark @else text-bg-secondary @endif">
                                        {{ ucfirst($org->status) }}
                                    </span>
                                    <span class="badge text-bg-secondary rounded-pill">{{ strtoupper($org->plan) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-secondary py-4">
                            <i class="bi bi-building fs-2 d-block mb-2 opacity-50"></i>
                            Nenhuma organização cadastrada.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-xl-5">
                <div class="surface-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h5 class="fw-semibold mb-1">Chamados em aberto</h5>
                            <p class="text-secondary small mb-0">Tickets por prioridade.</p>
                        </div>
                        <a href="{{ route('admin.support.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver todos</a>
                    </div>

                    @forelse ($openTickets as $ticket)
                        <div class="list-item mb-3">
                            <div class="d-flex align-items-start gap-3">
                                @php
                                    $pClass = match($ticket->priority) {
                                        'critica' => 'text-bg-danger',
                                        'alta'    => 'text-bg-warning text-dark',
                                        default   => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $pClass }} rounded-pill mt-1" style="min-width:58px;text-align:center;">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                                <div class="min-width-0">
                                    <div class="fw-semibold small text-truncate">{{ $ticket->title }}</div>
                                    <div class="text-secondary" style="font-size:0.78rem;">{{ $ticket->organization->name ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-secondary py-4">
                            <i class="bi bi-check-circle fs-2 d-block mb-2 text-success opacity-75"></i>
                            Nenhum chamado aberto.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection
