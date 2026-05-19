# Estrutura do Projeto — JusAI

> Laravel 11 + Bootstrap 5.3 + Supabase PostgreSQL + Vite  
> Multi-tenant SaaS jurídico — cada escritório é um tenant isolado por `organization_id`.

---

## Árvore de diretórios principais

```
jusai/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/                  # Controllers exclusivos do painel admin
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── OrganizationController.php
│   │   │   │   ├── FinanceController.php
│   │   │   │   ├── SupportController.php
│   │   │   │   └── LeadController.php
│   │   │   ├── DashboardController.php # Dashboard do cliente (org)
│   │   │   ├── LegalCaseController.php
│   │   │   ├── DocumentController.php
│   │   │   ├── AiReviewController.php
│   │   │   ├── DraftController.php
│   │   │   ├── SettingsController.php
│   │   │   └── ProfileController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureOrganizationAccess.php  # Bloqueia org sem tenant; redireciona super_admin
│   │   │   └── RoleMiddleware.php            # Verifica role do usuário (ex: super_admin)
│   │   └── Requests/
│   │       ├── StoreLegalCaseRequest.php
│   │       ├── UpdateLegalCaseRequest.php
│   │       ├── StoreDocumentRequest.php
│   │       └── TriggerAiReviewRequest.php
│   ├── Jobs/
│   │   └── ProcessAiReview.php         # Job assíncrono (queue) para chamar a Anthropic API
│   ├── Models/
│   │   ├── User.php
│   │   ├── Organization.php
│   │   ├── LegalCase.php
│   │   ├── Document.php
│   │   ├── Draft.php
│   │   ├── AiReview.php
│   │   ├── ActivityLog.php
│   │   ├── Subscription.php
│   │   ├── Invoice.php
│   │   ├── SupportTicket.php
│   │   ├── TicketMessage.php
│   │   ├── Lead.php
│   │   └── LeadInteraction.php
│   ├── Providers/
│   │   └── AppServiceProvider.php      # Registra singletons: AnthropicService, SupabaseStorageService
│   ├── Services/
│   │   ├── AnthropicService.php        # Wrapper para a API da Anthropic (claude-haiku / claude-sonnet)
│   │   └── SupabaseStorageService.php  # Wrapper para o Storage REST da Supabase
│   └── Traits/
│       └── OrganizationScoped.php      # Helpers de multi-tenant: orgId(), scopedQuery(), logActivity()
├── config/
│   ├── jusai.php                       # Configurações da marca, IA e limites do produto
│   └── services.php                    # anthropic.key lido de .env
├── database/
│   ├── migrations/                     # Uma migration por tabela (ordem numérica)
│   └── seeders/
│       └── DatabaseSeeder.php          # Seed de orgs, usuários e dados iniciais
├── docs/
│   ├── ESTRUTURA.md                    # Este arquivo
│   └── BANCO_DE_DADOS.md               # Dicionário de dados (tabelas e colunas)
├── resources/
│   ├── css/
│   │   ├── app.css                     # Estilos globais (shell, sidebar, cards, skeleton…)
│   │   └── modules/                    # Um arquivo CSS por módulo — carregado só na página que precisa
│   │       ├── dashboard.css
│   │       ├── casos.css
│   │       ├── documentos.css
│   │       ├── revisor.css
│   │       ├── minutas.css
│   │       ├── configuracoes.css
│   │       └── admin/
│   │           ├── dashboard.css
│   │           ├── organizations.css
│   │           ├── finance.css
│   │           ├── support.css
│   │           └── leads.css
│   ├── js/
│   │   ├── app.js                      # JS global (sidebar toggle, tema dark, toast, tooltips)
│   │   └── modules/                    # Um arquivo JS por módulo — carregado só onde necessário
│   │       ├── casos-show.js           # Persiste aba ativa via URL hash
│   │       ├── documentos-create.js    # Auto-preenche título a partir do nome do arquivo
│   │       ├── revisor-index.js        # Mostra/oculta campos conforme tipo de análise
│   │       ├── revisor-show.js         # Polling do status da análise de IA (via data-status-url)
│   │       └── admin/
│   │           ├── dashboard.js        # Reservado para gráficos (fase N)
│   │           └── organizations.js    # Reservado para ordenação de tabela (fase N)
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php           # Layout principal do cliente (sidebar + navbar + @yield)
│       │   ├── admin.blade.php         # Layout do painel admin
│       │   ├── guest.blade.php         # Layout de páginas sem autenticação (login, registro)
│       │   └── partials/
│       │       ├── sidebar.blade.php         # Sidebar do cliente (desktop + mobile offcanvas)
│       │       ├── sidebar-nav.blade.php     # Loop de itens de navegação da sidebar
│       │       ├── navbar.blade.php          # Topbar do cliente (busca, ações, avatar + dropdown)
│       │       ├── admin-sidebar.blade.php   # Sidebar do admin
│       │       └── admin-navbar.blade.php    # Topbar do admin
│       ├── auth/                       # Login, registro, redefinição de senha (Breeze base)
│       ├── dashboard/
│       │   └── index.blade.php         # Dashboard do cliente com métricas reais
│       ├── casos/
│       │   ├── index.blade.php         # Lista paginada com filtros
│       │   ├── create.blade.php        # Formulário de novo caso
│       │   ├── show.blade.php          # Detalhe com abas: Documentos / Análises / Detalhes
│       │   └── edit.blade.php          # Edição do caso
│       ├── documentos/
│       │   ├── index.blade.php
│       │   ├── create.blade.php        # Upload com auto-fill de título + link ao caso
│       │   ├── show.blade.php          # Download (signed URL) + resumo de IA + análises
│       │   └── edit.blade.php
│       ├── revisor/
│       │   ├── index.blade.php         # Formulário de nova análise + histórico recente
│       │   └── show.blade.php          # Resultado da IA com skeleton enquanto processa
│       ├── minutas/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   ├── show.blade.php
│       │   └── edit.blade.php
│       ├── configuracoes/
│       │   └── index.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── organizations/
│       │   │   └── index.blade.php
│       │   ├── finance/
│       │   │   └── index.blade.php
│       │   ├── support/
│       │   │   └── index.blade.php
│       │   └── leads/
│       │       ├── index.blade.php
│       │       └── comparison.blade.php
│       └── placeholders/
│           └── module.blade.php        # Template genérico para módulos em construção
├── routes/
│   ├── web.php                         # Rotas client (auth + org.access) + admin (role:super_admin)
│   └── auth.php                        # Rotas de autenticação (Breeze)
└── vite.config.js                      # Entry points: app.css, app.js + todos os módulos
```

---

## Como os assets são carregados

Cada view carrega **apenas seus próprios CSS e JS**. O layout só carrega os globais.

### Layout (`app.blade.php` / `admin.blade.php`)
```html
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')   <!-- CSS de módulo injetado pela view -->
</head>
<body>
    ...
    @stack('scripts')  <!-- JS de módulo injetado pela view -->
</body>
```

### View de módulo (exemplo: `revisor/index.blade.php`)
```blade
@push('styles')
    @vite(['resources/css/modules/revisor.css'])
@endpush

@push('scripts')
    @vite(['resources/js/modules/revisor-index.js'])
@endpush
```

### Regra: o que vai em cada lugar

| Arquivo | Responsabilidade |
|---|---|
| `app.css` | Shell, sidebar, topbar, cards globais (`surface-card`, `stat-card`, `list-item`, `skeleton…`) |
| `modules/{módulo}.css` | Componentes e variáveis exclusivas do módulo |
| `app.js` | Sidebar toggle, tema dark/light, toast global, tooltips Bootstrap |
| `modules/{módulo}.js` | Lógica interativa isolada da view (polling, formulários condicionais, etc.) |

---

## Fluxo de autenticação e acesso

```
GET /  →  redirect /dashboard

/dashboard  (middleware: auth, org.access)
    └── EnsureOrganizationAccess
            ├── super_admin  →  redirect /admin
            ├── sem org / inativo  →  abort 403
            └── org válida  →  passa

/admin/*  (middleware: auth, role:super_admin)
    └── RoleMiddleware  →  verifica user->role === 'super_admin'
```

### Roles disponíveis

| Role | Acesso |
|---|---|
| `super_admin` | Painel `/admin` — visão global de todas as orgs |
| `org_admin` | Painel `/dashboard` — gestão completa do escritório |
| `lawyer` | Painel `/dashboard` — casos, docs e análises do escritório |
| `assistant` | Painel `/dashboard` — acesso limitado (sem configurações) |

---

## Serviços externos

### Anthropic (IA)
- Chamada via `AnthropicService` (singleton registrado em `AppServiceProvider`)
- `model_fast` (`claude-haiku-4-5-20251001`) → resumo_caso (rápido e barato)
- `model_strong` (`claude-sonnet-4-6`) → analise_documento, revisao_minuta, pesquisa_juridica
- Sempre assíncrono: o controller cria `AiReview(status=processando)`, despacha `ProcessAiReview` para a queue, redireciona imediatamente
- O job tem `tries=3` e `timeout=120s`; em falha, seta `status=erro`

### Supabase Storage
- Chamada via `SupabaseStorageService` (singleton)
- Bucket: `case-documents`
- Path convention: `{org_id}/{case_id|standalone}/{uuid}-{slug}.{ext}`
- Downloads via signed URL (expiração 1h)

### Queue
- Driver: `database` (tabela `jobs` já migrada)
- Para rodar localmente: `php artisan queue:work`

---

## Convenções de código

- **Multi-tenant**: todo controller de negócio usa `OrganizationScoped` — nunca query sem `where('organization_id', $this->orgId())`
- **PKs**: domínio de negócio usa UUID (`HasUuids`); `users`, `activity_logs`, `ticket_messages`, `lead_interactions` usam bigint autoincrement
- **FKs para users**: `unsignedBigInteger` (não UUID) para bater com o `users.id`
- **ActivityLog**: `$timestamps = false`, sempre passar `created_at` explicitamente
- **AiReview.result**: coluna NOT NULL — criar sempre com `result: ''`; o job preenche
- **Views**: padrão `@extends` / `@section` / `@yield` — não usar `$slot` (Livewire/Blade components)
