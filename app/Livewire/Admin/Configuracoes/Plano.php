<?php

namespace App\Livewire\Admin\Configuracoes;

use App\Models\AiReview;
use App\Models\Document;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Plano extends Component
{
    // ─── Metadados dos planos ───────────────────────────────────────────────────

    private array $plansMeta = [
        'trial' => [
            'name'          => 'Trial',
            'description'   => 'Período de avaliação gratuita',
            'color'         => 'gold',
            'price_monthly' => null,
            'price_annual'  => null,
            'limits'        => ['cases' => 10, 'documents' => 30, 'ai_analyses' => 15, 'members' => 3],
        ],
        'starter' => [
            'name'          => 'Starter',
            'description'   => 'Para advogados autônomos e pequenas bancas',
            'color'         => 'blue',
            'price_monthly' => 19700,   // centavos — R$ 197,00
            'price_annual'  => 177300,  // centavos — R$ 1.773,00 (25% off)
            'limits'        => ['cases' => 50, 'documents' => 200, 'ai_analyses' => 50, 'members' => 5],
        ],
        'professional' => [
            'name'          => 'Profissional',
            'description'   => 'Para escritórios em crescimento',
            'color'         => 'purple',
            'price_monthly' => 49700,
            'price_annual'  => 447300,
            'limits'        => ['cases' => 300, 'documents' => 1000, 'ai_analyses' => 200, 'members' => 20],
        ],
        'enterprise' => [
            'name'          => 'Enterprise',
            'description'   => 'Para grandes escritórios — limites personalizados',
            'color'         => 'dark',
            'price_monthly' => null,    // sob consulta
            'price_annual'  => null,
            'limits'        => ['cases' => -1, 'documents' => -1, 'ai_analyses' => -1, 'members' => -1],
        ],
    ];

    // ─── Computed: plano atual ──────────────────────────────────────────────────

    #[Computed]
    public function meta(): array
    {
        $plan = Auth::user()->organization?->plan ?? 'trial';

        return $this->plansMeta[$plan] ?? $this->plansMeta['trial'];
    }

    #[Computed]
    public function planKey(): string
    {
        return Auth::user()->organization?->plan ?? 'trial';
    }

    // ─── Computed: assinatura ───────────────────────────────────────────────────

    #[Computed]
    public function subscription(): ?\App\Models\Subscription
    {
        return Auth::user()->organization?->subscription;
    }

    // ─── Computed: uso atual ────────────────────────────────────────────────────

    #[Computed]
    public function usage(): array
    {
        $org = Auth::user()->organization;

        if (! $org) {
            return ['cases' => 0, 'documents' => 0, 'ai_analyses' => 0, 'members' => 0];
        }

        return [
            'cases'       => LegalCase::where('organization_id', $org->id)->count(),
            'documents'   => Document::where('organization_id', $org->id)->count(),
            'ai_analyses' => AiReview::where('organization_id', $org->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'members'     => User::where('organization_id', $org->id)
                ->where('is_active', true)
                ->count(),
        ];
    }

    // ─── Computed: faturas ──────────────────────────────────────────────────────

    #[Computed]
    public function invoices()
    {
        $org = Auth::user()->organization;

        if (! $org) {
            return collect();
        }

        return \App\Models\Invoice::where('organization_id', $org->id)
            ->latest('due_date')
            ->limit(12)
            ->get();
    }

    // ─── Helpers públicos (chamados pela view) ──────────────────────────────────

    /**
     * Percentual de uso (0–100). -1 = ilimitado.
     */
    public function usagePct(string $metric): int
    {
        $limit = $this->meta['limits'][$metric] ?? 0;

        if ($limit === -1) {
            return 0; // ilimitado
        }

        if ($limit === 0) {
            return 100;
        }

        return (int) min(100, round(($this->usage[$metric] / $limit) * 100));
    }

    /**
     * Classe CSS do fill da barra (warning/danger/vazio).
     */
    public function usageBarClass(string $metric): string
    {
        $pct = $this->usagePct($metric);

        if ($pct >= 90) {
            return 'danger';
        }

        if ($pct >= 70) {
            return 'warning';
        }

        return '';
    }

    /**
     * Label do valor de limite.
     */
    public function limitLabel(string $metric): string
    {
        $limit = $this->meta['limits'][$metric] ?? 0;

        return $limit === -1 ? '∞' : number_format($limit);
    }

    /**
     * Formata centavos para BRL.
     */
    public function formatBrl(int $cents): string
    {
        return 'R$ ' . number_format($cents / 100, 2, ',', '.');
    }

    /**
     * Nome legível do plano a partir da chave.
     */
    public function planLabel(string $key): string
    {
        return $this->plansMeta[$key]['name'] ?? ucfirst($key);
    }

    // ─── Render ─────────────────────────────────────────────────────────────────

    public function placeholder(): string
    {
        return <<<'HTML'
        <div>
            <div class="settings-skeleton-card placeholder-glow mb-3">
                <div class="placeholder col-5 rounded-3 mb-3" style="height:1.1rem"></div>
                <div class="placeholder col-12 rounded-3 mb-2" style="height:.9rem"></div>
                <div class="placeholder col-8 rounded-3 mb-2" style="height:.9rem"></div>
                <div class="placeholder col-6 rounded-3" style="height:.9rem"></div>
            </div>
            <div class="settings-skeleton-card placeholder-glow">
                <div class="placeholder col-4 rounded-3 mb-3" style="height:1.1rem"></div>
                <div class="placeholder col-12 rounded-3 mb-2" style="height:.9rem"></div>
                <div class="placeholder col-7 rounded-3" style="height:.9rem"></div>
            </div>
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.admin.configuracoes.plano');
    }
}
