<div>

    {{-- ───────────────────────────────────────────────────────────────────────────
         Seção Plano — cabeçalho
    ─────────────────────────────────────────────────────────────────────────── --}}
    <div class="settings-section-header mb-4">
        <div>
            <h2 class="mb-0">Plano &amp; Faturamento</h2>
            <p class="text-muted mb-0 small">Seu plano atual, uso do período, faturas e pagamento.</p>
        </div>
    </div>

    {{-- ───────────────────────────────────────────────────────────────────────────
         Card: Plano atual
    ─────────────────────────────────────────────────────────────────────────── --}}
    <div class="plan-card settings-card mb-4 plan-card--{{ $this->planKey }}">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">

            {{-- Info principal --}}
            <div>
                <div class="plan-badge plan-badge--{{ $this->planKey }} mb-2">
                    <i class="bi bi-patch-check-fill"></i>
                    {{ $this->meta['name'] }}
                </div>

                <p class="mb-2 text-muted small">{{ $this->meta['description'] }}</p>

                {{-- Preço --}}
                @if($this->planKey === 'trial')
                    <p class="mb-0 fw-semibold">
                        <i class="bi bi-gift me-1 text-warning"></i>
                        Gratuito durante o período de avaliação
                    </p>
                    @if(Auth::user()->organization?->trial_ends_at)
                        <p class="mb-0 small text-muted mt-1">
                            Expira em
                            <strong>{{ Auth::user()->organization->trial_ends_at->format('d/m/Y') }}</strong>
                            ({{ Auth::user()->organization->trial_ends_at->diffForHumans() }})
                        </p>
                    @endif

                @elseif($this->planKey === 'enterprise')
                    <p class="mb-0 fw-semibold">Precificação personalizada</p>
                    <p class="mb-0 small text-muted mt-1">Entre em contato com o comercial</p>

                @elseif($this->subscription)
                    <div class="d-flex align-items-baseline gap-2 flex-wrap">
                        <span class="plan-price">
                            @if($this->subscription->billing_cycle === 'annual')
                                {{ $this->formatBrl($this->meta['price_annual']) }}
                                <span class="plan-price-cycle">/ano</span>
                            @else
                                {{ $this->formatBrl($this->meta['price_monthly']) }}
                                <span class="plan-price-cycle">/mês</span>
                            @endif
                        </span>
                        @if($this->subscription->billing_cycle === 'annual')
                            <span class="badge bg-success text-white" style="font-size:0.72rem">
                                25% off
                            </span>
                        @endif
                    </div>

                    {{-- Renovação --}}
                    @if($this->subscription->current_period_end)
                        <p class="mb-0 small text-muted mt-1">
                            @if($this->subscription->canceled_at)
                                <i class="bi bi-x-circle-fill text-danger me-1"></i>
                                Cancelado — acesso até
                                <strong>{{ $this->subscription->current_period_end->format('d/m/Y') }}</strong>
                            @else
                                <i class="bi bi-arrow-repeat me-1"></i>
                                Renova em
                                <strong>{{ $this->subscription->current_period_end->format('d/m/Y') }}</strong>
                            @endif
                        </p>
                    @endif

                @else
                    {{-- Plano ativo mas sem assinatura registrada (migrado manualmente, etc.) --}}
                    @if($this->meta['price_monthly'])
                        <p class="mb-0 fw-semibold">
                            {{ $this->formatBrl($this->meta['price_monthly']) }}
                            <span class="plan-price-cycle">/mês</span>
                        </p>
                    @endif
                @endif
            </div>

            {{-- Status da assinatura --}}
            <div class="d-flex flex-column align-items-end gap-2">
                @if($this->subscription)
                    @php
                        $statusMap = [
                            'active'    => ['label' => 'Ativa',      'cls' => 'bg-success'],
                            'trialing'  => ['label' => 'Trial',       'cls' => 'bg-warning text-dark'],
                            'past_due'  => ['label' => 'Vencida',     'cls' => 'bg-danger'],
                            'canceled'  => ['label' => 'Cancelada',   'cls' => 'bg-secondary'],
                            'paused'    => ['label' => 'Pausada',     'cls' => 'bg-secondary'],
                        ];
                        $st = $statusMap[$this->subscription->status] ?? ['label' => $this->subscription->status, 'cls' => 'bg-secondary'];
                    @endphp
                    <span class="badge {{ $st['cls'] }}" style="font-size:0.75rem;padding:0.4em 0.85em">
                        {{ $st['label'] }}
                    </span>
                @elseif($this->planKey === 'trial')
                    <span class="badge bg-warning text-dark" style="font-size:0.75rem;padding:0.4em 0.85em">
                        Trial
                    </span>
                @endif

                {{-- CTA --}}
                @if($this->planKey !== 'enterprise')
                    <a href="#" class="btn btn-sm btn-outline-primary" tabindex="-1"
                       title="Entre em contato para fazer upgrade">
                        <i class="bi bi-arrow-up-circle me-1"></i>
                        Fazer upgrade
                    </a>
                @else
                    <a href="mailto:comercial@jusai.com.br" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-envelope me-1"></i>
                        Falar com comercial
                    </a>
                @endif
            </div>

        </div>
    </div>

    {{-- ───────────────────────────────────────────────────────────────────────────
         Card: Uso do período
    ─────────────────────────────────────────────────────────────────────────── --}}
    <div class="settings-card mb-4">
        <h3 class="settings-card-title mb-4">
            <i class="bi bi-bar-chart-line me-2"></i>Uso do período
        </h3>

        @php
            $usageMetrics = [
                'cases'       => ['label' => 'Casos',             'icon' => 'bi-briefcase'],
                'documents'   => ['label' => 'Documentos',        'icon' => 'bi-file-earmark-text'],
                'ai_analyses' => ['label' => 'Análises IA (mês)', 'icon' => 'bi-cpu'],
                'members'     => ['label' => 'Membros ativos',    'icon' => 'bi-people'],
            ];
        @endphp

        <div class="row g-4">
            @foreach($usageMetrics as $metric => $info)
                @php
                    $pct      = $this->usagePct($metric);
                    $barClass = $this->usageBarClass($metric);
                    $used     = $this->usage[$metric];
                    $limit    = $this->meta['limits'][$metric];
                    $isUnlim  = $limit === -1;
                @endphp
                <div class="col-sm-6">
                    <div class="usage-metric-card">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="usage-metric-label">
                                <i class="bi {{ $info['icon'] }} me-1"></i>
                                {{ $info['label'] }}
                            </span>
                            <span class="usage-metric-count {{ $barClass ? 'usage-metric-count--'.$barClass : '' }}">
                                {{ number_format($used) }}
                                @if($isUnlim)
                                    <span class="text-muted fw-normal">/ ∞</span>
                                @else
                                    <span class="text-muted fw-normal">/ {{ $this->limitLabel($metric) }}</span>
                                @endif
                            </span>
                        </div>

                        <div class="usage-bar-track">
                            <div class="usage-bar-fill {{ $barClass }}"
                                 style="width: {{ $isUnlim ? 8 : $pct }}%"
                                 title="{{ $isUnlim ? 'Ilimitado' : $pct.'%' }}">
                            </div>
                        </div>

                        @if(! $isUnlim && $pct >= 90)
                            <p class="usage-metric-alert text-danger mt-1 mb-0 small">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Limite quase atingido. Considere fazer upgrade.
                            </p>
                        @elseif(! $isUnlim && $pct >= 70)
                            <p class="usage-metric-alert text-warning mt-1 mb-0 small">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>
                                Uso elevado ({{ $pct }}%).
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($this->planKey === 'trial')
            <div class="alert alert-warning d-flex align-items-center gap-2 mt-4 mb-0" role="alert"
                 style="border-radius:0.85rem;font-size:0.875rem">
                <i class="bi bi-info-circle-fill flex-shrink-0"></i>
                <span>
                    Os limites do Trial são menores que os planos pagos.
                    <a href="#" class="alert-link">Faça upgrade agora</a> para continuar usando sem interrupções.
                </span>
            </div>
        @endif
    </div>

    {{-- ───────────────────────────────────────────────────────────────────────────
         Card: Método de pagamento
    ─────────────────────────────────────────────────────────────────────────── --}}
    @if(! in_array($this->planKey, ['trial', 'enterprise']))
        <div class="settings-card mb-4">
            <h3 class="settings-card-title mb-3">
                <i class="bi bi-credit-card me-2"></i>Método de pagamento
            </h3>

            @if($this->subscription?->payment_method || $this->subscription?->gateway_subscription_id)
                <div class="payment-method-row d-flex align-items-center gap-3">
                    <div class="payment-method-icon">
                        <i class="bi bi-credit-card-2-front-fill fs-4"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-semibold">Cartão cadastrado</p>
                        <p class="mb-0 small text-muted">
                            Gerenciado pelo gateway de pagamento
                            @if($this->subscription->payment_gateway)
                                ({{ ucfirst($this->subscription->payment_gateway) }})
                            @endif
                        </p>
                    </div>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Em breve">
                            <i class="bi bi-pencil me-1"></i>Alterar
                        </button>
                    </div>
                </div>
            @else
                <div class="payment-method-empty d-flex align-items-center gap-3 py-2">
                    <div class="payment-method-icon payment-method-icon--empty">
                        <i class="bi bi-credit-card fs-4"></i>
                    </div>
                    <div>
                        <p class="mb-0 fw-semibold">Nenhum método cadastrado</p>
                        <p class="mb-0 small text-muted">
                            A integração com gateway de pagamento estará disponível em breve.
                        </p>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-secondary" style="font-size:0.72rem">Em breve</span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- ───────────────────────────────────────────────────────────────────────────
         Card: Histórico de faturas
    ─────────────────────────────────────────────────────────────────────────── --}}
    <div class="settings-card">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="settings-card-title mb-0">
                <i class="bi bi-receipt me-2"></i>Histórico de faturas
            </h3>
            @if($this->invoices->isNotEmpty())
                <span class="badge bg-secondary" style="font-size:0.72rem">
                    Últimas {{ $this->invoices->count() }}
                </span>
            @endif
        </div>

        @if($this->invoices->isEmpty())
            {{-- Empty state --}}
            <div class="invoice-empty-state text-center py-5">
                <div class="invoice-empty-icon mb-3">
                    <i class="bi bi-receipt fs-1 text-muted" style="opacity:.35"></i>
                </div>
                <p class="text-muted mb-0">Nenhuma fatura encontrada.</p>
                <p class="text-muted small">As faturas aparecerão aqui após a primeira cobrança.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover invoice-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col" style="width:130px">Referência</th>
                            <th scope="col">Descrição</th>
                            <th scope="col" style="width:120px">Vencimento</th>
                            <th scope="col" style="width:110px">Valor</th>
                            <th scope="col" style="width:110px">Status</th>
                            <th scope="col" style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->invoices as $invoice)
                            @php
                                $statusInvoice = match($invoice->status) {
                                    'paid'       => ['label' => 'Paga',      'cls' => 'badge-invoice--paid'],
                                    'open'       => ['label' => 'Em aberto', 'cls' => 'badge-invoice--open'],
                                    'past_due'   => ['label' => 'Vencida',   'cls' => 'badge-invoice--due'],
                                    'void'       => ['label' => 'Cancelada', 'cls' => 'badge-invoice--void'],
                                    'draft'      => ['label' => 'Rascunho',  'cls' => 'badge-invoice--draft'],
                                    default      => ['label' => $invoice->status, 'cls' => 'badge-invoice--draft'],
                                };
                            @endphp
                            <tr>
                                <td class="invoice-ref font-monospace">
                                    {{ $invoice->reference_number ?? '#' . substr($invoice->id, 0, 8) }}
                                </td>
                                <td class="text-muted small">
                                    Plano {{ $this->planLabel($invoice->subscription?->plan ?? $this->planKey) }}
                                    @if($invoice->subscription?->billing_cycle === 'annual')
                                        · anual
                                    @elseif($invoice->subscription?->billing_cycle === 'monthly')
                                        · mensal
                                    @endif
                                    @if($invoice->notes)
                                        — {{ Str::limit($invoice->notes, 40) }}
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="fw-semibold">
                                    {{ $this->formatBrl($invoice->amount_cents) }}
                                </td>
                                <td>
                                    <span class="badge-invoice {{ $statusInvoice['cls'] }}">
                                        {{ $statusInvoice['label'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($invoice->status === 'paid')
                                        <button type="button"
                                                class="btn btn-link btn-sm p-0 text-muted"
                                                title="Baixar recibo"
                                                disabled>
                                            <i class="bi bi-download"></i>
                                        </button>
                                    @elseif($invoice->status === 'open' || $invoice->status === 'past_due')
                                        <a href="#" class="btn btn-link btn-sm p-0 text-primary"
                                           title="Pagar fatura">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

@assets
<style>
/* ─── Plan color variants ──────────────────────────────────────────────────── */

/* Blue — Starter */
.plan-card--starter {
    border-color: rgba(37, 99, 235, 0.3);
    background: rgba(37, 99, 235, 0.04);
}
.plan-badge--starter {
    background: rgba(37, 99, 235, 0.1);
    color: #1d4ed8;
}
[data-theme="dark"] .plan-card--starter {
    border-color: rgba(96, 165, 250, 0.25);
    background: rgba(96, 165, 250, 0.06);
}
[data-theme="dark"] .plan-badge--starter {
    background: rgba(96, 165, 250, 0.14);
    color: #93c5fd;
}

/* Purple — Profissional */
.plan-card--professional {
    border-color: rgba(126, 34, 206, 0.3);
    background: rgba(126, 34, 206, 0.04);
}
.plan-badge--professional {
    background: rgba(126, 34, 206, 0.1);
    color: #6d28d9;
}
[data-theme="dark"] .plan-card--professional {
    border-color: rgba(192, 132, 252, 0.25);
    background: rgba(192, 132, 252, 0.06);
}
[data-theme="dark"] .plan-badge--professional {
    background: rgba(192, 132, 252, 0.14);
    color: #c084fc;
}

/* Dark — Enterprise */
.plan-card--enterprise {
    border-color: rgba(30, 41, 59, 0.25);
    background: rgba(30, 41, 59, 0.04);
}
.plan-badge--enterprise {
    background: rgba(30, 41, 59, 0.12);
    color: #334155;
}
[data-theme="dark"] .plan-card--enterprise {
    border-color: rgba(148, 163, 184, 0.2);
    background: rgba(148, 163, 184, 0.05);
}
[data-theme="dark"] .plan-badge--enterprise {
    background: rgba(148, 163, 184, 0.12);
    color: #94a3b8;
}

/* Trial — gold (already handled by .plan-card default in configuracoes.css) */
.plan-card--trial  { /* inherits .plan-card */ }
.plan-badge--trial { /* inherits .plan-badge */ }

/* ─── Plan price ───────────────────────────────────────────────────────────── */

.plan-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--jusai-text, #1a1f2e);
    line-height: 1;
}

.plan-price-cycle {
    font-size: 0.875rem;
    font-weight: 400;
    color: var(--jusai-text-muted, #6b7280);
}

[data-theme="dark"] .plan-price {
    color: rgba(255,255,255,0.9);
}

/* ─── Usage metrics ────────────────────────────────────────────────────────── */

.usage-metric-card {
    padding: 1rem 1.25rem;
    border: 1px solid rgba(215, 220, 229, 0.6);
    border-radius: 1rem;
    background: rgba(255,255,255,0.6);
}

[data-theme="dark"] .usage-metric-card {
    border-color: rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.03);
}

.usage-metric-label {
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--jusai-text-muted, #6b7280);
}

.usage-metric-count {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--jusai-text, #1a1f2e);
}

.usage-metric-count--warning {
    color: #d97706;
}

.usage-metric-count--danger {
    color: #b91c1c;
}

[data-theme="dark"] .usage-metric-count {
    color: rgba(255,255,255,0.9);
}

[data-theme="dark"] .usage-metric-count--warning {
    color: #fbbf24;
}

[data-theme="dark"] .usage-metric-count--danger {
    color: #f87171;
}

/* ─── Invoice table ────────────────────────────────────────────────────────── */

.invoice-table {
    font-size: 0.875rem;
}

.invoice-table thead th {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--jusai-text-muted, #6b7280);
    border-bottom-color: rgba(215, 220, 229, 0.5);
    padding-top: 0;
}

[data-theme="dark"] .invoice-table thead th {
    color: rgba(255,255,255,0.4);
    border-bottom-color: rgba(255,255,255,0.06);
}

.invoice-table tbody tr {
    vertical-align: middle;
}

.invoice-table tbody td {
    border-color: rgba(215, 220, 229, 0.35);
}

[data-theme="dark"] .invoice-table tbody td {
    border-color: rgba(255,255,255,0.05);
}

.invoice-ref {
    font-size: 0.75rem;
    color: var(--jusai-text-muted, #6b7280);
}

/* Invoice status badges */
.badge-invoice {
    display: inline-flex;
    align-items: center;
    padding: 0.3em 0.75em;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.01em;
}

.badge-invoice--paid {
    background: rgba(22, 163, 74, 0.12);
    color: #15803d;
}

.badge-invoice--open {
    background: rgba(37, 99, 235, 0.1);
    color: #1d4ed8;
}

.badge-invoice--due {
    background: rgba(185, 28, 28, 0.1);
    color: #b91c1c;
}

.badge-invoice--void {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

.badge-invoice--draft {
    background: rgba(107, 114, 128, 0.08);
    color: #9ca3af;
}

[data-theme="dark"] .badge-invoice--paid {
    background: rgba(22, 163, 74, 0.18);
    color: #4ade80;
}

[data-theme="dark"] .badge-invoice--open {
    background: rgba(96, 165, 250, 0.15);
    color: #93c5fd;
}

[data-theme="dark"] .badge-invoice--due {
    background: rgba(248, 113, 113, 0.15);
    color: #f87171;
}

[data-theme="dark"] .badge-invoice--void,
[data-theme="dark"] .badge-invoice--draft {
    background: rgba(255,255,255,0.07);
    color: rgba(255,255,255,0.45);
}

/* ─── Payment method ───────────────────────────────────────────────────────── */

.payment-method-row,
.payment-method-empty {
    padding: 0.75rem 1rem;
    border: 1px solid rgba(215, 220, 229, 0.5);
    border-radius: 0.85rem;
    background: rgba(255,255,255,0.5);
}

[data-theme="dark"] .payment-method-row,
[data-theme="dark"] .payment-method-empty {
    border-color: rgba(255,255,255,0.07);
    background: rgba(255,255,255,0.03);
}

.payment-method-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.6rem;
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
    flex-shrink: 0;
}

.payment-method-icon--empty {
    background: rgba(107, 114, 128, 0.08);
    color: #9ca3af;
}

[data-theme="dark"] .payment-method-icon {
    background: rgba(96, 165, 250, 0.12);
    color: #93c5fd;
}

[data-theme="dark"] .payment-method-icon--empty {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.3);
}
</style>
@endassets
