# Banco de Dados — JusAI

> PostgreSQL via Supabase (connection pooler porta 6543, `sslmode=require`)  
> Todas as tabelas de domínio usam **UUID** como PK, exceto onde indicado.  
> Tenant isolation: toda tabela de negócio tem `organization_id` (FK para `organizations`).

---

## Diagrama de relacionamentos

```
organizations
    ├── users (N)               organization_id → organizations.id
    ├── legal_cases (N)         organization_id → organizations.id
    │   ├── documents (N)       legal_case_id   → legal_cases.id
    │   ├── drafts (N)          legal_case_id   → legal_cases.id
    │   └── ai_reviews (N)      legal_case_id   → legal_cases.id
    │       ├── document_id     → documents.id
    │       └── draft_id        → drafts.id
    ├── activity_logs (N)       organization_id → organizations.id
    ├── subscriptions (N)       organization_id → organizations.id
    │   └── invoices (N)        subscription_id → subscriptions.id
    └── support_tickets (N)     organization_id → organizations.id
        └── ticket_messages (N) ticket_id       → support_tickets.id

leads (sem org — pipeline de pré-venda)
    └── lead_interactions (N)   lead_id         → leads.id
```

---

## Tabelas

---

### `users`
Usuários do sistema. PK é **bigint autoincrement** (padrão Laravel).  
Atualizada pela migration `alter_users_add_tenant_columns` para adicionar campos de tenant.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | bigint AI | sim | PK autoincrement |
| `organization_id` | uuid | não | FK → `organizations.id`. `null` para `super_admin` |
| `role` | enum | sim | Papel do usuário: `super_admin`, `org_admin`, `lawyer`, `assistant` |
| `is_active` | boolean | sim | `false` bloqueia login mesmo com credenciais válidas |
| `name` | string | sim | Nome completo |
| `email` | string unique | sim | E-mail de login |
| `email_verified_at` | timestamp | não | Preenchido ao verificar e-mail |
| `password` | string | sim | Hash bcrypt |
| `remember_token` | string | não | Token de "lembrar-me" |
| `created_at` / `updated_at` | timestamp | auto | Gerenciado pelo Laravel |

**Roles:**
- `super_admin` — acesso total ao painel `/admin`; sem `organization_id`
- `org_admin` — administrador do escritório; pode gerenciar usuários e configurações
- `lawyer` — advogado; acessa casos, documentos e análises
- `assistant` — assistente; acesso somente leitura em algumas áreas

---

### `organizations`
Escritórios de advocacia — cada um é um **tenant** isolado.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `name` | string | sim | Nome do escritório |
| `slug` | string unique | sim | Identificador URL-friendly (ex: `silva-associados`) |
| `email` | string | sim | E-mail de contato do escritório |
| `phone` | string | não | Telefone |
| `document` | string | não | CNPJ ou CPF do titular |
| `status` | enum | sim | `trial` · `active` · `suspended` · `canceled` |
| `plan` | enum | sim | `starter` · `professional` · `enterprise` |
| `trial_ends_at` | timestamp | não | Data de vencimento do período trial |
| `created_at` / `updated_at` | timestamp | auto | |

---

### `legal_cases`
Dossiês jurídicos — unidade central de trabalho de um escritório.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `organization_id` | uuid | sim | FK → `organizations.id` (cascade delete) |
| `title` | string | sim | Nome do caso (ex: "João Silva vs Empresa X") |
| `client_name` | string | sim | Nome completo do cliente |
| `client_email` | string | não | E-mail do cliente |
| `client_phone` | string | não | Telefone do cliente |
| `area` | enum | sim | Área do Direito: `civil` · `trabalhista` · `empresarial` · `tributario` · `criminal` · `familia` · `outros` |
| `status` | enum | sim | `triagem` · `em_andamento` · `aguardando_cliente` · `aguardando_tribunal` · `encerrado` · `arquivado` |
| `risk_level` | enum | sim | Nível de risco: `baixo` · `medio` · `alto` · `critico` |
| `description` | text | não | Descrição pública do caso |
| `internal_notes` | text | não | Notas internas (não visíveis ao cliente) |
| `assigned_to` | bigint | não | FK → `users.id` — advogado responsável (null on delete) |
| `created_by` | bigint | sim | FK → `users.id` — quem criou o caso |
| `opened_at` | date | sim | Data de abertura do caso |
| `closed_at` | timestamp | não | Data de encerramento |
| `created_at` / `updated_at` | timestamp | auto | |

---

### `documents`
Arquivos enviados ao sistema (PDF, DOCX, TXT). Armazenados no Supabase Storage.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `organization_id` | uuid | sim | FK → `organizations.id` (cascade delete) |
| `legal_case_id` | uuid | não | FK → `legal_cases.id` (null on delete). `null` = documento avulso |
| `title` | string | sim | Título descritivo do documento |
| `original_filename` | string | sim | Nome original do arquivo enviado pelo usuário |
| `storage_path` | string | sim | Caminho no bucket Supabase: `{org_id}/{case_id}/{uuid}-{slug}.{ext}` |
| `file_size` | bigint | sim | Tamanho em bytes |
| `mime_type` | string | sim | MIME type (ex: `application/pdf`) |
| `status` | enum | sim | `uploading` → `processing` → `ready` · `error` |
| `ai_summary` | text | não | Resumo executivo gerado pela IA (primeiros 1.000 chars do resultado) |
| `ai_extracted_at` | timestamp | não | Quando a IA terminou de processar o documento |
| `uploaded_by` | bigint | sim | FK → `users.id` |
| `created_at` / `updated_at` | timestamp | auto | |

**Fluxo de status:**
1. `uploading` — arquivo sendo enviado ao Supabase Storage
2. `processing` — `ProcessAiReview` job despachado para análise de IA
3. `ready` — job concluiu; `ai_summary` e `ai_extracted_at` preenchidos
4. `error` — job falhou após 3 tentativas

---

### `drafts`
Minutas e rascunhos jurídicos (gerados por IA ou redigidos manualmente).

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `organization_id` | uuid | sim | FK → `organizations.id` (cascade delete) |
| `legal_case_id` | uuid | não | FK → `legal_cases.id` (null on delete) |
| `title` | string | sim | Título da minuta |
| `type` | enum | sim | `notificacao_extrajudicial` · `contrato` · `peticao_inicial` · `contestacao` · `recurso` · `parecer` · `outros` |
| `content` | text | sim | Conteúdo completo da minuta |
| `status` | enum | sim | `rascunho` · `em_revisao` · `aprovado` · `rejeitado` · `publicado` |
| `version` | smallint | sim | Versão atual (começa em 1, incrementa em cada revisão aprovada) |
| `generated_by_ai` | boolean | sim | `true` se o conteúdo foi gerado pela Anthropic |
| `ai_model_used` | string | não | Identificador do modelo (ex: `claude-sonnet-4-6`) |
| `reviewed_by` | bigint | não | FK → `users.id` — quem aprovou/rejeitou |
| `reviewed_at` | timestamp | não | Quando foi revisado |
| `created_by` | bigint | sim | FK → `users.id` |
| `created_at` / `updated_at` | timestamp | auto | |

---

### `ai_reviews`
Análises de IA realizadas sobre documentos, minutas ou casos.  
Cada análise é assíncrona — nasce com `status=processando` e é concluída pelo job.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `organization_id` | uuid | sim | FK → `organizations.id` (cascade delete) |
| `legal_case_id` | uuid | não | FK → `legal_cases.id` (null on delete) |
| `document_id` | uuid | não | FK → `documents.id` (null on delete). Documento analisado |
| `draft_id` | uuid | não | FK → `drafts.id` (null on delete). Minuta revisada |
| `type` | enum | sim | `analise_documento` · `revisao_minuta` · `pesquisa_juridica` · `resumo_caso` |
| `prompt_used` | text | sim | Prompt enviado à IA (para auditoria e depuração) |
| `result` | text | sim | Resposta da IA. Criado como `''`; preenchido pelo job |
| `status` | enum | sim | `processando` · `concluido` · `erro` · `cancelado` |
| `ai_model_used` | string | sim | Modelo usado (ex: `claude-haiku-4-5-20251001`) |
| `tokens_used` | integer | não | Total de tokens (input + output) consumidos |
| `confidence_score` | decimal(3,2) | não | Score de confiança da IA (0.00–1.00). Reservado para uso futuro |
| `requires_human_review` | boolean | sim | Sempre `true` — toda análise exige validação humana antes de uso externo |
| `reviewed_by` | bigint | não | FK → `users.id` — advogado que confirmou a análise |
| `reviewed_at` | timestamp | não | Quando foi confirmada a revisão humana |
| `created_by` | bigint | sim | FK → `users.id` — quem disparou a análise |
| `created_at` / `updated_at` | timestamp | auto | |

**Tipos de análise e modelo usado:**
| Tipo | Modelo | Descrição |
|---|---|---|
| `resumo_caso` | `claude-haiku-4-5-20251001` | Resumo executivo de documento vinculado ao caso |
| `analise_documento` | `claude-sonnet-4-6` | Cláusulas-chave, riscos e recomendações |
| `revisao_minuta` | `claude-sonnet-4-6` | Inconsistências, ambiguidades e sugestões |
| `pesquisa_juridica` | `claude-sonnet-4-6` | Fundamentação em legislação e jurisprudência |

---

### `activity_logs`
Auditoria de todas as ações relevantes do sistema. PK é **bigint** (volume alto).  
**Sem `updated_at`** — `$timestamps = false` no model; sempre passar `created_at` explicitamente.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | bigint AI | sim | PK autoincrement |
| `organization_id` | uuid | não | FK → `organizations.id` (null on delete). `null` em ações de sistema |
| `user_id` | bigint | não | FK → `users.id` (null on delete). `null` em ações automáticas |
| `event` | string | sim | Identificador do evento (ex: `caso.criado`, `documento.enviado`) |
| `description` | text | sim | Descrição legível da ação |
| `subject_type` | string | não | Tipo do objeto afetado (ex: `LegalCase`, `Document`) |
| `subject_id` | uuid | não | ID do objeto afetado |
| `metadata` | jsonb | não | Dados extras em JSON (ex: campos alterados, valores anteriores) |
| `ip_address` | string | não | IP de origem da requisição |
| `created_at` | timestamp | sim | Definido manualmente com `now()` (sem `updated_at`) |

---

### `subscriptions`
Assinaturas ativas de cada escritório. Um escritório pode ter mais de uma assinatura histórica.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `organization_id` | uuid | sim | FK → `organizations.id` (cascade delete) |
| `plan` | enum | sim | `starter` · `professional` · `enterprise` |
| `status` | enum | sim | `trial` · `active` · `past_due` · `canceled` · `paused` |
| `billing_cycle` | enum | sim | `monthly` · `annual` |
| `price_cents` | integer | sim | Valor em centavos (ex: `29900` = R$ 299,00) |
| `currency` | char(3) | sim | Padrão `BRL` |
| `trial_ends_at` | timestamp | não | Fim do período trial |
| `current_period_start` | timestamp | sim | Início do período de cobrança vigente |
| `current_period_end` | timestamp | sim | Fim do período de cobrança vigente |
| `canceled_at` | timestamp | não | Quando foi cancelada |
| `payment_gateway` | string | não | Ex: `stripe`, `iugu`, `mercadopago` |
| `gateway_subscription_id` | string | não | ID da assinatura no gateway externo |
| `created_at` / `updated_at` | timestamp | auto | |

---

### `invoices`
Faturas emitidas para cada escritório.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `organization_id` | uuid | sim | FK → `organizations.id` (cascade delete) |
| `subscription_id` | uuid | não | FK → `subscriptions.id` (null on delete) |
| `reference_number` | string unique | sim | Número de referência legível (ex: `INV-2026-0042`) |
| `amount_cents` | integer | sim | Valor total em centavos |
| `currency` | char(3) | sim | Padrão `BRL` |
| `status` | enum | sim | `pending` · `paid` · `failed` · `refunded` · `canceled` |
| `due_date` | date | sim | Data de vencimento |
| `paid_at` | timestamp | não | Quando foi paga |
| `payment_method` | string | não | Ex: `credit_card`, `boleto`, `pix` |
| `gateway_invoice_id` | string | não | ID da fatura no gateway externo |
| `notes` | text | não | Observações internas |
| `created_at` / `updated_at` | timestamp | auto | |

---

### `support_tickets`
Chamados de suporte abertos por escritórios para a equipe JusAI.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `organization_id` | uuid | sim | FK → `organizations.id` (cascade delete) |
| `opened_by` | bigint | sim | FK → `users.id` — quem abriu o chamado |
| `assigned_to` | bigint | não | FK → `users.id` — agente de suporte responsável |
| `title` | string | sim | Título do chamado |
| `description` | text | sim | Descrição detalhada do problema |
| `status` | enum | sim | `aberto` · `em_andamento` · `aguardando_cliente` · `resolvido` · `fechado` |
| `priority` | enum | sim | `baixa` · `media` · `alta` · `critica` |
| `category` | enum | sim | `tecnico` · `financeiro` · `duvida` · `sugestao` · `bug` · `outros` |
| `resolution_notes` | text | não | Notas da resolução (visíveis ao cliente) |
| `first_response_at` | timestamp | não | Quando o agente respondeu pela primeira vez (SLA) |
| `resolved_at` | timestamp | não | Quando foi marcado como resolvido |
| `closed_at` | timestamp | não | Quando foi fechado definitivamente |
| `created_at` / `updated_at` | timestamp | auto | |

---

### `ticket_messages`
Mensagens trocadas dentro de um chamado. PK é **bigint** (volume alto).  
**Sem `updated_at`** — apenas `created_at`.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | bigint AI | sim | PK autoincrement |
| `ticket_id` | uuid | sim | FK → `support_tickets.id` (cascade delete) |
| `sender_id` | bigint | sim | FK → `users.id` — autor da mensagem |
| `message` | text | sim | Conteúdo da mensagem |
| `is_internal` | boolean | sim | `true` = nota interna (visível só para agentes, não para o cliente) |
| `created_at` | timestamp | sim | Definido automaticamente com `useCurrent()` |

---

### `leads`
Prospectos de novos escritórios — pipeline de pré-venda. **Não tem `organization_id`** (pré-conversão).

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | uuid | sim | PK |
| `name` | string | sim | Nome do contato |
| `email` | string | não | E-mail do contato |
| `phone` | string | não | Telefone |
| `company_name` | string | não | Nome do escritório prospectado |
| `company_size` | enum | não | Porte: `pequeno` · `medio` · `grande` |
| `area_of_interest` | text | não | Áreas do Direito de interesse |
| `source` | enum | sim | Origem: `website` · `indicacao` · `linkedin` · `evento` · `google_ads` · `cold_outreach` · `outros` |
| `status` | enum | sim | `novo` · `contatado` · `qualificado` · `demo_agendada` · `proposta_enviada` · `negociando` · `ganho` · `perdido` · `inativo` |
| `lost_reason` | string | não | Motivo da perda (preenchido quando `status=perdido`) |
| `estimated_value_cents` | integer | não | Valor estimado do contrato em centavos |
| `assigned_to` | bigint | não | FK → `users.id` — vendedor responsável |
| `notes` | text | não | Notas livres sobre o lead |
| `converted_at` | timestamp | não | Quando virou cliente (criou conta) |
| `converted_organization_id` | uuid | não | FK → `organizations.id` — org criada na conversão |
| `created_at` / `updated_at` | timestamp | auto | |

---

### `lead_interactions`
Histórico de interações com cada lead (e-mails, ligações, demos, etc.). PK é **bigint**.  
**Sem `updated_at`** — apenas `created_at`.

| Coluna | Tipo | Obrigatório | Descrição |
|---|---|---|---|
| `id` | bigint AI | sim | PK autoincrement |
| `lead_id` | uuid | sim | FK → `leads.id` (cascade delete) |
| `user_id` | bigint | sim | FK → `users.id` — quem realizou a interação |
| `type` | enum | sim | `email` · `ligacao` · `reuniao` · `demo` · `proposta` · `linkedin` · `outros` |
| `notes` | text | não | Anotações sobre a interação |
| `outcome` | string | não | Resultado obtido (ex: "Agendou demo para 25/06") |
| `scheduled_at` | timestamp | não | Data/hora do próximo contato ou compromisso |
| `created_at` | timestamp | sim | Definido automaticamente com `useCurrent()` |

---

## Tabelas de infraestrutura Laravel

### `jobs`
Fila de tarefas assíncronas. Usada pelo `ProcessAiReview` job.  
Gerenciada automaticamente pelo Laravel Queue. Não alterar manualmente.

### `cache` / `cache_locks`
Cache de sessão e throttle. Gerenciadas pelo Laravel.

### `sessions`
Sessões de usuário autenticado.

| Coluna | Descrição |
|---|---|
| `id` | ID de sessão (string, PK) |
| `user_id` | FK → `users.id` (nullable) |
| `ip_address` | IP do cliente |
| `user_agent` | User-agent do browser |
| `payload` | Dados da sessão (serializado) |
| `last_activity` | Unix timestamp da última atividade |

### `password_reset_tokens`
Tokens para redefinição de senha.

---

## Tipos de chave primária

| Padrão | Tabelas |
|---|---|
| **UUID** (`uuid`) | `organizations`, `legal_cases`, `documents`, `drafts`, `ai_reviews`, `subscriptions`, `invoices`, `support_tickets`, `leads` |
| **Bigint autoincrement** | `users`, `activity_logs`, `ticket_messages`, `lead_interactions`, `jobs`, `sessions` |

> **Por quê misturar?** As tabelas de domínio de negócio usam UUID para evitar enumeração e facilitar sincronização distribuída. As tabelas de volume alto (logs, mensagens) usam bigint para performance em índices sequenciais.
