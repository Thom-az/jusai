<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiReview;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AiMetricsController extends Controller
{
    // Custo estimado por token (USD) — blended input/output 40/60
    private const COST_PER_TOKEN = [
        'claude-haiku-4-5-20251001' => 0.0000024,  // $0.80 in + $4.00 out /MTok
        'claude-sonnet-4-6'         => 0.0000102,  // $3.00 in + $15.00 out /MTok
        'default'                   => 0.0000050,
    ];

    public function index(): View
    {
        // ── Totais gerais ─────────────────────────────────────────
        $totalReviews   = AiReview::where('status', 'concluido')->count();
        $reviewsErro    = AiReview::where('status', 'erro')->count();
        $totalChatConvs = AiConversation::count();
        $totalChatMsgs  = AiMessage::where('role', 'assistant')
            ->whereNotNull('tokens_used')
            ->where('tokens_used', '>', 0)
            ->count();

        // ── Tokens por modelo (análises) ──────────────────────────
        $reviewTokensByModel = AiReview::where('status', 'concluido')
            ->whereNotNull('ai_model_used')
            ->select('ai_model_used', DB::raw('sum(tokens_used) as total_tokens'), DB::raw('count(*) as total_calls'))
            ->groupBy('ai_model_used')
            ->get()
            ->keyBy('ai_model_used');

        // ── Tokens de chat (modelo fast = Haiku) ─────────────────
        $chatTokens = AiMessage::where('role', 'assistant')->sum('tokens_used') ?? 0;

        // ── Custo estimado ────────────────────────────────────────
        $estimatedCostUsd = 0.0;

        foreach ($reviewTokensByModel as $model => $row) {
            $rate = self::COST_PER_TOKEN[$model] ?? self::COST_PER_TOKEN['default'];
            $estimatedCostUsd += $row->total_tokens * $rate;
        }

        // Chat usa Haiku
        $estimatedCostUsd += $chatTokens * self::COST_PER_TOKEN['claude-haiku-4-5-20251001'];

        $totalTokens = $reviewTokensByModel->sum('total_tokens') + $chatTokens;

        // ── Análises por tipo ─────────────────────────────────────
        $reviewsByType = AiReview::where('status', 'concluido')
            ->select('type', DB::raw('count(*) as total'), DB::raw('sum(tokens_used) as tokens'), 'ai_model_used')
            ->groupBy('type', 'ai_model_used')
            ->orderBy('type')
            ->get()
            ->groupBy('type')
            ->map(function ($rows) {
                return [
                    'total'  => $rows->sum('total'),
                    'tokens' => $rows->sum('tokens'),
                    'model'  => $rows->first()->ai_model_used ?? '—',
                ];
            });

        // ── Uso por organização (top 10) ──────────────────────────
        $orgUsageRaw = AiReview::where('status', 'concluido')
            ->select(
                'organization_id',
                DB::raw('count(*) as reviews'),
                DB::raw('sum(tokens_used) as tokens'),
                DB::raw('max(ai_model_used) as last_model'),
            )
            ->groupBy('organization_id')
            ->orderByDesc('tokens')
            ->limit(10)
            ->get();

        $orgIds       = $orgUsageRaw->pluck('organization_id');
        $organizations = Organization::whereIn('id', $orgIds)->get()->keyBy('id');

        $orgUsage = $orgUsageRaw->map(function ($row) use ($organizations) {
            $row->organization  = $organizations->get($row->organization_id);
            $model              = $row->last_model ?? 'default';
            $rate               = self::COST_PER_TOKEN[$model] ?? self::COST_PER_TOKEN['default'];
            $row->estimated_cost = $row->tokens * $rate;
            return $row;
        });

        // ── Análises recentes ─────────────────────────────────────
        $recentReviews = AiReview::with(['legalCase', 'creator', 'organization'])
            ->whereIn('status', ['concluido', 'erro'])
            ->orderByDesc('updated_at')
            ->limit(15)
            ->get();

        // ── Taxa de uso (últimos 30 dias) ─────────────────────────
        $reviewsLast30 = AiReview::where('status', 'concluido')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $typeLabels = $this->typeLabels();

        return view('admin.ia.index', compact(
            'totalReviews',
            'reviewsErro',
            'totalChatConvs',
            'totalChatMsgs',
            'reviewTokensByModel',
            'chatTokens',
            'estimatedCostUsd',
            'totalTokens',
            'reviewsByType',
            'orgUsage',
            'recentReviews',
            'reviewsLast30',
            'typeLabels',
        ));
    }

    private function typeLabels(): array
    {
        return [
            'resumo_caso'       => 'Resumo de caso',
            'analise_documento' => 'Análise de documento',
            'revisao_minuta'    => 'Revisão de minuta',
            'pesquisa_juridica' => 'Pesquisa jurídica',
            'rascunho_minuta'   => 'Rascunho de minuta',
        ];
    }
}
