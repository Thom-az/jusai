<?php

return [
    'brand' => [
        'name' => env('APP_NAME', 'JusAI'),
        'legal_name' => env('APP_LEGAL_NAME', env('APP_NAME', 'JusAI')),
        'tagline' => env('APP_TAGLINE', 'Copiloto jurídico com IA'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'mock'),
        'default_model' => env('AI_DEFAULT_MODEL', 'mock-legal-copilot'),
        'temperature' => (float) env('AI_TEMPERATURE', 0.2),
        'detail_level' => env('AI_DETAIL_LEVEL', 'standard'),
        'model_fast'   => env('AI_MODEL_FAST',   'claude-haiku-4-5-20251001'),
        'model_strong' => env('AI_MODEL_STRONG', 'claude-sonnet-4-6'),
        'require_human_review' => filter_var(env('AI_REQUIRE_HUMAN_REVIEW', true), FILTER_VALIDATE_BOOL),
        'require_sources' => filter_var(env('AI_REQUIRE_SOURCES', true), FILTER_VALIDATE_BOOL),
        'block_without_basis' => filter_var(env('AI_BLOCK_WITHOUT_BASIS', true), FILTER_VALIDATE_BOOL),
        'review_notice' => env('AI_REVIEW_NOTICE', 'Conteúdo gerado por IA para apoio operacional. Revisão humana por profissional habilitado é obrigatória.'),
        'draft_notice' => env('AI_DRAFT_NOTICE', 'Documento gerado como rascunho. Revisão por profissional habilitado é obrigatória.'),
    ],

    'shell' => [
        'user' => [
            'name' => 'Juliana Souza',
            'role' => 'Administradora do escritório',
            'initials' => 'JS',
        ],

        'navigation' => [
            [
                'label' => 'Operação',
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'bi-grid', 'route' => 'dashboard', 'pattern' => 'dashboard'],
                    ['label' => 'Casos', 'icon' => 'bi-briefcase', 'route' => 'cases.index', 'pattern' => 'casos*'],
                    ['label' => 'Documentos', 'icon' => 'bi-file-earmark-text', 'route' => 'documents.index', 'pattern' => 'documentos*'],
                    ['label' => 'Minutas', 'icon' => 'bi-journal-richtext', 'route' => 'drafts.index', 'pattern' => 'minutas*'],
                    ['label' => 'Assistente IA', 'icon' => 'bi-chat-dots', 'route' => 'chat.index', 'pattern' => 'chat*'],
                    ['label' => 'Revisor Jurídico', 'icon' => 'bi-shield-check', 'route' => 'review.index', 'pattern' => 'revisor*'],
                ],
            ],
            [
                'label' => 'Escritório',
                'items' => [
                    ['label' => 'Chamados', 'icon' => 'bi-headset', 'route' => 'tickets.index', 'pattern' => 'chamados*'],
                    ['label' => 'Configurações', 'icon' => 'bi-sliders', 'route' => 'settings.index', 'pattern' => 'configuracoes*'],
                ],
            ],
        ],

        'admin_navigation' => [
            [
                'label' => 'Visão Geral',
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'bi-grid', 'route' => 'admin.dashboard', 'pattern' => 'admin'],
                    ['label' => 'Organizações', 'icon' => 'bi-building', 'route' => 'admin.organizations.index', 'pattern' => 'admin/organizations*'],
                ],
            ],
            [
                'label' => 'Gestão',
                'items' => [
                    ['label' => 'Financeiro', 'icon' => 'bi-currency-dollar', 'route' => 'admin.finance.index', 'pattern' => 'admin/financeiro*'],
                    ['label' => 'Chamados', 'icon' => 'bi-headset', 'route' => 'admin.support.index', 'pattern' => 'admin/chamados*'],
                    ['label' => 'Leads', 'icon' => 'bi-person-lines-fill', 'route' => 'admin.leads.index', 'pattern' => 'admin/leads*'],
                ],
            ],
        ],
    ],
];
