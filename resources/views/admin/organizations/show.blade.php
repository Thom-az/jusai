@extends('layouts.admin')

@section('title', $organization->name)

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
            <a href="{{ route('admin.organizations.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div class="flex-grow-1">
                <h2 class="fw-semibold mb-1">{{ $organization->name }}</h2>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill @if($organization->status === 'active') text-bg-success @elseif($organization->status === 'trial') text-bg-warning text-dark @else text-bg-secondary @endif">
                        {{ ucfirst($organization->status) }}
                    </span>
                    <span class="badge text-bg-secondary rounded-pill">{{ strtoupper($organization->plan) }}</span>
                </div>
            </div>
            <a href="{{ route('admin.organizations.edit', $organization) }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-pencil me-1"></i>Editar
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                {{-- Informações gerais --}}
                <div class="surface-card p-4 mb-4">
                    <h5 class="fw-semibold mb-3">Informações do escritório</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Nome legal</div>
                            <div>{{ $organization->legal_name ?? $organization->name }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">E-mail</div>
                            <div>{{ $organization->email ?? '—' }}</div>
                        </div>
                        @if ($organization->phone)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">Telefone</div>
                                <div>{{ $organization->phone }}</div>
                            </div>
                        @endif
                        @if ($organization->document)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">CNPJ / CPF</div>
                                <div>{{ $organization->document }}</div>
                            </div>
                        @endif
                        @if ($organization->city)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">Cidade</div>
                                <div>{{ $organization->city }}{{ $organization->state ? ', ' . $organization->state : '' }}</div>
                            </div>
                        @endif
                        <div class="col-sm-6">
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Criado em</div>
                            <div>{{ $organization->created_at->format('d/m/Y') }}</div>
                        </div>
                        @if ($organization->trial_ends_at)
                            <div class="col-sm-6">
                                <div class="text-secondary small text-uppercase fw-semibold mb-1">Trial expira em</div>
                                <div class="{{ $organization->trial_ends_at->isPast() ? 'text-danger' : '' }}">
                                    {{ $organization->trial_ends_at->format('d/m/Y') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Usuários --}}
                <div class="surface-card p-4 mb-4">
                    <h5 class="fw-semibold mb-3">Usuários <span class="badge text-bg-secondary">{{ $organization->users->count() }}</span></h5>
                    @forelse ($organization->users as $user)
                        <div class="d-flex align-items-center gap-3 py-2" style="border-bottom: 1px solid rgba(215,220,229,0.5);">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-semibold"
                                 style="width:2rem;height:2rem;font-size:.75rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-medium small text-truncate">{{ $user->name }}</div>
                                <div class="text-secondary" style="font-size:.78rem;">{{ $user->email }}</div>
                            </div>
                            <span class="badge text-bg-secondary rounded-pill small">{{ $user->role ?? '—' }}</span>
                        </div>
                    @empty
                        <p class="text-secondary small">Nenhum usuário cadastrado.</p>
                    @endforelse
                </div>

                {{-- Chamados recentes --}}
                @if ($organization->supportTickets->isNotEmpty())
                    <div class="surface-card p-4">
                        <h5 class="fw-semibold mb-3">Chamados recentes</h5>
                        @foreach ($organization->supportTickets->sortByDesc('created_at')->take(5) as $ticket)
                            <div class="d-flex align-items-center gap-3 py-2" style="border-bottom: 1px solid rgba(215,220,229,0.5);">
                                <div class="flex-grow-1 min-width-0">
                                    <div class="fw-medium small text-truncate">{{ $ticket->title }}</div>
                                    <div class="text-secondary" style="font-size:.78rem;">{{ $ticket->protocol }} &bull; {{ $ticket->created_at->diffForHumans() }}</div>
                                </div>
                                @php
                                    $pClass = match($ticket->priority) {
                                        'critica' => 'text-bg-danger',
                                        'alta'    => 'text-bg-warning text-dark',
                                        default   => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $pClass }} rounded-pill small">{{ ucfirst($ticket->priority) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="col-lg-4">
                {{-- Assinatura --}}
                <div class="surface-card p-4 mb-4">
                    <h6 class="fw-semibold mb-3">Assinatura</h6>
                    @if ($organization->subscriptions->isNotEmpty())
                        @foreach ($organization->subscriptions->sortByDesc('created_at')->take(1) as $sub)
                            <dl class="row mb-0 small">
                                <dt class="col-5 text-secondary text-uppercase">Plano</dt>
                                <dd class="col-7">{{ strtoupper($sub->plan ?? $organization->plan) }}</dd>

                                <dt class="col-5 text-secondary text-uppercase">Ciclo</dt>
                                <dd class="col-7">{{ $sub->billing_cycle ?? '—' }}</dd>

                                <dt class="col-5 text-secondary text-uppercase">Valor</dt>
                                <dd class="col-7">R$ {{ number_format(($sub->price_cents ?? 0) / 100, 2, ',', '.') }}</dd>

                                <dt class="col-5 text-secondary text-uppercase">Status</dt>
                                <dd class="col-7">
                                    <span class="badge {{ $sub->status === 'active' ? 'text-bg-success' : 'text-bg-secondary' }} rounded-pill">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </dd>
                            </dl>
                        @endforeach
                    @else
                        <p class="text-secondary small mb-0">Sem assinatura ativa.</p>
                    @endif
                </div>

                {{-- Métricas de uso --}}
                <div class="surface-card p-4">
                    <h6 class="fw-semibold mb-3">Uso</h6>
                    <dl class="row mb-0 small">
                        <dt class="col-7 text-secondary">Casos</dt>
                        <dd class="col-5 text-end fw-semibold">{{ $organization->legalCases()->count() }}</dd>

                        <dt class="col-7 text-secondary">Documentos</dt>
                        <dd class="col-5 text-end fw-semibold">{{ $organization->documents()->count() }}</dd>

                        <dt class="col-7 text-secondary">Minutas</dt>
                        <dd class="col-5 text-end fw-semibold">{{ $organization->drafts()->count() }}</dd>

                        <dt class="col-7 text-secondary">Análises de IA</dt>
                        <dd class="col-5 text-end fw-semibold">{{ $organization->aiReviews()->count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
