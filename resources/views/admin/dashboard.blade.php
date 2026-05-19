@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="container-fluid px-0">

        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Painel Administrativo</h2>
                <p class="text-secondary mb-0 small">Visao geral de MRR, suporte e pipeline de vendas.</p>
            </div>
            <span class="badge text-bg-danger px-3 py-2 rounded-pill">
                <i class="bi bi-shield-lock-fill me-1"></i>Acesso restrito — Admin JusAI
            </span>
        </div>

        <div class="row g-3 mb-4">
            @foreach ($metrics as $m)
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
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

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="fw-semibold mb-0">Organizacoes recentes</h6>
                            <a href="{{ route('admin.organizations.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver todas</a>
                        </div>

                        @forelse ($recentOrganizations as $org)
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div>
                                    <div class="fw-medium">{{ $org->name }}</div>
                                    <div class="small text-secondary">{{ $org->email }}</div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge @if($org->status === 'active') text-bg-success @elseif($org->status === 'trial') text-bg-warning @else text-bg-secondary @endif rounded-pill">
                                        {{ $org->status }}
                                    </span>
                                    <span class="badge text-bg-light border rounded-pill">{{ $org->plan }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary mb-0">Nenhuma organizacao cadastrada.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <h6 class="fw-semibold mb-0">Chamados em aberto</h6>
                            <a href="{{ route('admin.support.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Ver todos</a>
                        </div>

                        @forelse ($openTickets as $ticket)
                            <div class="d-flex align-items-start gap-3 py-2 border-bottom">
                                <span class="badge @if($ticket->priority === 'critica') text-bg-danger @elseif($ticket->priority === 'alta') text-bg-warning @else text-bg-secondary @endif rounded-pill mt-1" style="min-width: 56px; text-align:center;">
                                    {{ $ticket->priority }}
                                </span>
                                <div>
                                    <div class="fw-medium small">{{ $ticket->title }}</div>
                                    <div class="small text-secondary">{{ $ticket->organization->name ?? '—' }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary mb-0">Nenhum chamado aberto.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
