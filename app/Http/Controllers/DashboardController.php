<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index', [
            'metrics' => [
                [
                    'label' => 'Casos ativos',
                    'value' => 18,
                    'trend' => '+3 este mes',
                    'icon' => 'bi-briefcase',
                    'icon_class' => 'icon-blue',
                ],
                [
                    'label' => 'Documentos analisados',
                    'value' => 247,
                    'trend' => '91% processados',
                    'icon' => 'bi-file-earmark-text',
                    'icon_class' => 'icon-gold',
                ],
                [
                    'label' => 'Minutas geradas',
                    'value' => 36,
                    'trend' => '12 em revisao',
                    'icon' => 'bi-journal-richtext',
                    'icon_class' => 'icon-green',
                ],
                [
                    'label' => 'Alertas pendentes',
                    'value' => 7,
                    'trend' => '2 com prioridade alta',
                    'icon' => 'bi-exclamation-triangle',
                    'icon_class' => 'icon-red',
                ],
            ],
            'recentCases' => [
                [
                    'title' => 'Acao de cobranca - Construtora Horizonte',
                    'client' => 'Construtora Horizonte',
                    'area' => 'Civel',
                    'status' => 'Em andamento',
                    'status_class' => 'status-active',
                    'risk' => 'Medio',
                    'updated_at' => 'Atualizado ha 2 horas',
                ],
                [
                    'title' => 'Reclamatoria trabalhista - Grupo Vale Norte',
                    'client' => 'Grupo Vale Norte',
                    'area' => 'Trabalhista',
                    'status' => 'Ponto de atencao',
                    'status_class' => 'status-attention',
                    'risk' => 'Alto',
                    'updated_at' => 'Atualizado ha 5 horas',
                ],
                [
                    'title' => 'Revisao contratual - Clinica Aurora',
                    'client' => 'Clinica Aurora',
                    'area' => 'Empresarial',
                    'status' => 'Triagem',
                    'status_class' => 'status-neutral',
                    'risk' => 'Baixo',
                    'updated_at' => 'Atualizado ontem',
                ],
            ],
            'activities' => [
                [
                    'title' => 'Analise IA mockada gerada para o caso Construtora Horizonte',
                    'description' => 'Resumo executivo, riscos e proximos passos sugeridos foram atualizados.',
                    'time' => '10 min atras',
                ],
                [
                    'title' => 'Upload do PDF "Contrato_social_aurora.pdf"',
                    'description' => 'Documento recebido e encaminhado para processamento mockado.',
                    'time' => '32 min atras',
                ],
                [
                    'title' => 'Minuta de notificacao extrajudicial revisada',
                    'description' => 'Versao 2 enviada para analise humana do escritorio.',
                    'time' => '1 h atras',
                ],
                [
                    'title' => 'Novo caso cadastrado para Grupo Vale Norte',
                    'description' => 'Cadastro inicial concluido com area trabalhista e risco alto.',
                    'time' => 'Hoje, 08:45',
                ],
            ],
            'quickActions' => [
                [
                    'title' => 'Novo Caso',
                    'description' => 'Cadastrar um novo dossie juridico com contexto inicial.',
                    'icon' => 'bi-folder-plus',
                    'route' => route('cases.create'),
                ],
                [
                    'title' => 'Upload de Documento',
                    'description' => 'Anexar PDFs, DOCX e imagens para analise futura.',
                    'icon' => 'bi-cloud-arrow-up',
                    'route' => route('documents.index'),
                ],
                [
                    'title' => 'Nova Minuta',
                    'description' => 'Abrir o fluxo de geracao de rascunho juridico.',
                    'icon' => 'bi-magic',
                    'route' => route('drafts.index'),
                ],
                [
                    'title' => 'Revisar Peca',
                    'description' => 'Rodar o revisor mockado em uma peca selecionada.',
                    'icon' => 'bi-shield-check',
                    'route' => route('review.index'),
                ],
            ],
            'alerts' => [
                'Toda saida de IA deve ser validada por revisao humana antes de uso externo.',
                'Integracao com Supabase e provider real de IA ainda sera conectada nas proximas etapas.',
            ],
        ]);
    }
}
