@extends('layouts.admin')

@section('title', 'Métricas de IA')

@push('styles')
    @vite(['resources/css/modules/admin/ia.css'])
@endpush

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-semibold mb-1">Inteligência Artificial</h2>
            <p class="text-secondary mb-0 small">Consumo de tokens, custo estimado e histórico de operações.</p>
        </div>
        <span class="badge text-bg-primary rounded-pill px-3 py-2">
            <i class="bi bi-cpu me-1"></i>{{ config('jusai.ai.provider') === 'anthropic' ? 'Anthropic Claude' : 'Modo Mock' }}
        </span>
    </div>

    {{-- Aviso se em modo mock --}}
    @if (config('jusai.ai.provider') !== 'anthropic')
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-flask fs-5 flex-shrink-0"></i>
            <div class="small">
                <strong>Modo mock ativo.</strong> Configure <code>AI_PROVIDER=anthropic</code> e <code>ANTHROPIC_API_KEY</code> no <code>.env</code> para ativar a IA real.
                Os dados de tokens e custo abaixo refletem apenas chamadas reais registradas.
            </div>
        </div>
    @endif

    {{-- ── Cards de totais ─────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="ia-metric-card">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="ia-metric-label">Análises realizadas</div>
                        <div class="ia-metric-value">{{ number_format($totalReviews) }}</div>
                        <div class="ia-metric-sub">{{ $reviewsLast30 }} nos últimos 30 dias</div>
                    </div>
                    <div class="ia-metric-icon icon-blue"><i class="bi bi-cpu"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ia-metric-card">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="ia-metric-label">Tokens consumidos</div>
                        <div class="ia-metric-value">{{ number_format($totalTokens) }}</div>
                        <div class="ia-metric-sub">análises + conversas</div>
                    </div>
                    <div class="ia-metric-icon icon-purple"><i class="bi bi-lightning-charge"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ia-metric-card">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="ia-metric-label">Custo estimado</div>
                        <div class="ia-metric-value">US$ {{ number_format($estimatedCostUsd, 4) }}</div>
                        <div class="ia-metric-sub">blended input+output</div>
                    </div>
                    <div class="ia-metric-icon icon-green"><i class="bi bi-currency-dollar"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="ia-metric-card">
                <div class="d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <div class="ia-metric-label">Conversas de chat</div>
                        <div class="ia-metric-value">{{ number_format($totalChatConvs) }}</div>
                        <div class="ia-metric-sub">{{ number_format($totalChatMsgs) }} respostas geradas</div>
                    </div>
                    <div class="ia-metric-icon icon-orange"><i class="bi bi-chat-dots"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">

        {{-- ── Uso por tipo de operação ───────────────────────── --}}
        <div class="col-xl-6">
            <div class="surface-card p-4 h-100">
                <h5 class="fw-semibold mb-1">Uso por tipo de operação</h5>
                <p class="text-secondary small mb-3">Contagem e tokens por tipo de análise realizada.</p>

                @if ($reviewsByType->isEmpty())
                    <div class="text-center text-secondary py-4 small">
                        <i class="bi bi-cpu fs-2 d-block mb-2 opacity-50"></i>
                        Nenhuma análise registrada ainda.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small text-secondary text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.04em;">Tipo</th>
                                    <th class="small text-secondary text-uppercase fw-semibold text-end" style="font-size:.7rem;letter-spacing:.04em;">Chamadas</th>
                                    <th class="small text-secondary text-uppercase fw-semibold text-end" style="font-size:.7rem;letter-spacing:.04em;">Tokens</th>
                                    <th class="small text-secondary text-uppercase fw-semibold" style="font-size:.7rem;letter-spacing:.04em;">Modelo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviewsByType as $type => $data)
                                    <tr>
                                        <td class="small fw-semibold">{{ $typeLabels[$type] ?? $type }}</td>
                                        <td class="small text-end">{{ number_format($data['total']) }}</td>
                                        <td class="small text-end">{{ number_format($data['tokens']) }}</td>
                                        <td>
                                            @php $m = $data['model'] ?? '—'; @endphp
                                            <span class="badge rounded-pill {{ str_contains($m, 'haiku') ? 'text-bg-secondary' : 'text-bg-primary' }}" style="font-size:.68rem;">
                                                {{ str_contains($m, 'haiku') ? 'Haiku' : (str_contains($m, 'sonnet') ? 'Sonnet' : $m) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Uso por modelo ─────────────────────────────────── --}}
        <div class="col-xl-6">
            <div class="surface-card p-4 h-100">
                <h5 class="fw-semibold mb-1">Consumo por modelo</h5>
                <p class="text-secondary small mb-3">Tokens e custo estimado por modelo Claude utilizado.</p>

                @php
                    $haiku  = $reviewTokensByModel->get('claude-haiku-4-5-20251001');
                    $sonnet = $reviewTokensByModel->get('claude-sonnet-4-6');
                    $haikuCost  = ($haiku  ? $haiku->total_tokens  : 0) * 0.0000024;
                    $sonnetCost = ($sonnet ? $sonnet->total_tokens : 0) * 0.0000102;
                    $chatCost   = $chatTokens * 0.0000024;
                @endphp

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">Claude Haiku <span class="text-secondary fw-normal">(análises rápidas + chat)</span></span>
                        <span class="small text-secondary">US$ {{ number_format($haikuCost + $chatCost, 4) }}</span>
                    </div>
                    <div class="ia-token-row">
                        <span class="badge text-bg-secondary rounded-pill me-2">Haiku</span>
                        <span class="small">{{ number_format(($haiku ? $haiku->total_tokens : 0) + $chatTokens) }} tokens</span>
                        <span class="text-secondary small ms-auto">{{ $haiku ? number_format($haiku->total_calls) : 0 }} análises · {{ number_format($totalChatMsgs) }} msgs chat</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-semibold">Claude Sonnet <span class="text-secondary fw-normal">(análises complexas)</span></span>
                        <span class="small text-secondary">US$ {{ number_format($sonnetCost, 4) }}</span>
                    </div>
                    <div class="ia-token-row">
                        <span class="badge text-bg-primary rounded-pill me-2">Sonnet</span>
                        <span class="small">{{ number_format($sonnet ? $sonnet->total_tokens : 0) }} tokens</span>
                        <span class="text-secondary small ms-auto">{{ $sonnet ? number_format($sonnet->total_calls) : 0 }} análises</span>
                    </div>
                </div>

                <div class="p-3 rounded mt-3" style="background:rgba(37,99,235,.04);border:1px solid rgba(37,99,235,.12);">
                    <div class="small text-secondary">
                        <i class="bi bi-info-circle me-1 text-primary"></i>
                        Custo estimado com blended rate (40% input / 60% output).
                        Haiku: $0.80/$4.00 por MTok · Sonnet: $3.00/$15.00 por MTok.
                        Ative <strong>prompt caching</strong> nos prompts longos para reduzir até 90% no custo de input.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Uso por organização ─────────────────────────────────── --}}
    @if ($orgUsage->isNotEmpty())
        <div class="surface-card p-4 mb-4">
            <h5 class="fw-semibold mb-1">Uso por escritório</h5>
            <p class="text-secondary small mb-3">Top organizações por consumo de tokens.</p>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-secondary text-uppercase fw-semibold ps-3" style="font-size:.7rem;letter-spacing:.04em;">Escritório</th>
                            <th class="small text-secondary text-uppercase fw-semibold text-end" style="font-size:.7rem;letter-spacing:.04em;">Análises</th>
                            <th class="small text-secondary text-uppercase fw-semibold text-end" style="font-size:.7rem;letter-spacing:.04em;">Tokens</th>
                            <th class="small text-secondary text-uppercase fw-semibold text-end pe-3" style="font-size:.7rem;letter-spacing:.04em;">Custo est.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orgUsage as $row)
                            <tr>
                                <td class="small fw-semibold ps-3">{{ $row->organization?->name ?? '—' }}</td>
                                <td class="small text-end">{{ number_format($row->reviews) }}</td>
                                <td class="small text-end">{{ number_format($row->tokens) }}</td>
                                <td class="small text-end pe-3">US$ {{ number_format($row->estimated_cost, 4) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ── Análises recentes ───────────────────────────────────── --}}
    <div class="surface-card p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="fw-semibold mb-1">Histórico recente</h5>
                <p class="text-secondary small mb-0">Últimas 15 operações de IA registradas.</p>
            </div>
            @if ($reviewsErro > 0)
                <span class="badge text-bg-danger rounded-pill">
                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $reviewsErro }} com erro
                </span>
            @endif
        </div>

        @if ($recentReviews->isEmpty())
            <div class="text-center text-secondary py-4 small">
                <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-50"></i>
                Nenhuma operação registrada ainda.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small text-secondary text-uppercase fw-semibold ps-3" style="font-size:.7rem;letter-spacing:.04em;">Operação</th>
                            <th class="small text-secondary text-uppercase fw-semibold d-none d-md-table-cell" style="font-size:.7rem;letter-spacing:.04em;">Caso</th>
                            <th class="small text-secondary text-uppercase fw-semibold d-none d-lg-table-cell" style="font-size:.7rem;letter-spacing:.04em;">Modelo</th>
                            <th class="small text-secondary text-uppercase fw-semibold text-end" style="font-size:.7rem;letter-spacing:.04em;">Tokens</th>
                            <th class="small text-secondary text-uppercase fw-semibold text-end" style="font-size:.7rem;letter-spacing:.04em;">Status</th>
                            <th class="small text-secondary text-uppercase fw-semibold text-end pe-3 d-none d-lg-table-cell" style="font-size:.7rem;letter-spacing:.04em;">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentReviews as $review)
                            <tr>
                                <td class="small ps-3">{{ $typeLabels[$review->type] ?? $review->type }}</td>
                                <td class="small d-none d-md-table-cell text-truncate" style="max-width:200px;">
                                    {{ $review->legalCase?->title ?? '—' }}
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if ($review->ai_model_used)
                                        <span class="badge rounded-pill {{ str_contains($review->ai_model_used, 'haiku') ? 'text-bg-secondary' : 'text-bg-primary' }}" style="font-size:.68rem;">
                                            {{ str_contains($review->ai_model_used, 'haiku') ? 'Haiku' : (str_contains($review->ai_model_used, 'sonnet') ? 'Sonnet' : $review->ai_model_used) }}
                                        </span>
                                    @else
                                        <span class="text-secondary small">—</span>
                                    @endif
                                </td>
                                <td class="small text-end">{{ $review->tokens_used ? number_format($review->tokens_used) : '—' }}</td>
                                <td class="text-end">
                                    @if ($review->status === 'concluido')
                                        <span class="badge text-bg-success rounded-pill" style="font-size:.68rem;">Concluído</span>
                                    @elseif ($review->status === 'erro')
                                        <span class="badge text-bg-danger rounded-pill" style="font-size:.68rem;">Erro</span>
                                    @else
                                        <span class="badge text-bg-warning text-dark rounded-pill" style="font-size:.68rem;">{{ ucfirst($review->status) }}</span>
                                    @endif
                                </td>
                                <td class="small text-end pe-3 text-secondary d-none d-lg-table-cell">
                                    {{ $review->updated_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
