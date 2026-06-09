<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AiReview;
use App\Models\Document;
use App\Models\Draft;
use App\Models\LegalCase;
use App\Traits\OrganizationScoped;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use OrganizationScoped;

    public function index(): View
    {
        $orgId = $this->orgId();

        $casosAtivos = LegalCase::where('organization_id', $orgId)
            ->whereIn('status', ['triagem', 'em_andamento', 'aguardando_cliente', 'aguardando_prazo', 'em_recurso'])
            ->count();

        $docsReady = Document::where('organization_id', $orgId)
            ->where('status', 'ready')
            ->count();

        $minutasGeradas = Draft::where('organization_id', $orgId)
            ->where('generated_by_ai', true)
            ->count();

        $reviewsPending = AiReview::where('organization_id', $orgId)
            ->where('requires_human_review', true)
            ->whereNull('reviewed_at')
            ->where('status', 'concluido')
            ->count();

        $recentCases = LegalCase::where('organization_id', $orgId)
            ->with('creator')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        $activities = ActivityLog::where('organization_id', $orgId)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $metrics = [
            [
                'label'      => 'Casos ativos',
                'value'      => $casosAtivos,
                'trend'      => 'em andamento',
                'icon'       => 'bi-briefcase',
                'icon_class' => 'icon-blue',
            ],
            [
                'label'      => 'Documentos processados',
                'value'      => $docsReady,
                'trend'      => 'prontos para análise',
                'icon'       => 'bi-file-earmark-text',
                'icon_class' => 'icon-gold',
            ],
            [
                'label'      => 'Minutas geradas por IA',
                'value'      => $minutasGeradas,
                'trend'      => 'geradas pela IA',
                'icon'       => 'bi-journal-richtext',
                'icon_class' => 'icon-green',
            ],
            [
                'label'      => 'Análises pendentes de revisão',
                'value'      => $reviewsPending,
                'trend'      => 'aguardando revisão humana',
                'icon'       => 'bi-exclamation-triangle',
                'icon_class' => 'icon-red',
            ],
        ];

        return view('dashboard.index', [
            'metrics'      => $metrics,
            'recentCases'  => $recentCases,
            'activities'   => $activities,
            'quickActions' => [
                [
                    'title'       => 'Novo Caso',
                    'description' => 'Cadastrar um novo dossiê jurídico com contexto inicial.',
                    'icon'        => 'bi-folder-plus',
                    'route'       => route('cases.create'),
                ],
                [
                    'title'       => 'Upload de Documento',
                    'description' => 'Anexar PDFs e documentos para análise de IA.',
                    'icon'        => 'bi-cloud-arrow-up',
                    'route'       => route('documents.create'),
                ],
                [
                    'title'       => 'Nova Minuta',
                    'description' => 'Abrir o fluxo de geração de rascunho jurídico.',
                    'icon'        => 'bi-magic',
                    'route'       => route('drafts.create'),
                ],
                [
                    'title'       => 'Revisar Peça',
                    'description' => 'Rodar o revisor de IA em uma peça selecionada.',
                    'icon'        => 'bi-shield-check',
                    'route'       => route('review.index'),
                ],
            ],
            'alerts' => [
                config('jusai.ai.review_notice'),
            ],
        ]);
    }
}
