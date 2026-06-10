# JusAI — Copiloto Jurídico com IA

SaaS multi-tenant para escritórios de advocacia brasileiros. Centraliza gestão de casos, documentos e minutas com análise jurídica assistida por IA (Anthropic Claude).

## Funcionalidades

| Módulo | Descrição |
|--------|-----------|
| **Casos** | Abertura, acompanhamento e histórico de processos jurídicos |
| **Documentos** | Upload de PDF/DOCX com extração e análise automática via IA |
| **Minutas** | Geração de rascunhos (petição inicial, contestação, recurso, contrato, etc.) |
| **Revisor IA** | Análise de documentos e minutas: resumo, riscos, inconsistências, recomendações |
| **Chat Jurídico** | Assistente conversacional com contexto do caso |
| **Painel Admin** | Gestão de organizações, financeiro, leads e prompts de IA |
| **Configurações** | Perfil, segurança (2FA), preferências, equipe e faturamento |

## Stack

**Backend**
- PHP 8.3 + Laravel 13
- Livewire 4 (UI reativa sem SPA)
- Spatie Permission (RBAC)
- Google 2FA (TOTP)
- Queue jobs com backoff exponencial

**Frontend**
- Tailwind CSS + Bootstrap 5
- Alpine.js
- Vite

**Infra / Dados**
- PostgreSQL via Supabase (multi-tenant com `organization_id` em todas as tabelas)
- Supabase Storage (arquivos de casos e minutas)
- Queue database-backed (suporte a Redis/SQS)

**IA**
- Anthropic Claude (`claude-haiku-4-5-20251001` para tarefas rápidas, `claude-sonnet-4-6` para análises complexas)
- Prompts jurídicos brasileiros (CF/88, CPC/2015, CC/2002, CDC, CLT, OAB)
- Modo mock para desenvolvimento sem consumir API

## Arquitetura de IA

O `AnthropicService` encapsula todas as chamadas à API Anthropic com:
- Prompts configuráveis em runtime via tabela `ai_prompts` (com fallback para `config/ai_prompts.php`)
- Temperature 0.2 para consistência jurídica
- Flag obrigatória de revisão humana em todo conteúdo gerado
- Extração de texto de PDF/DOCX antes do envio

As análises são processadas de forma assíncrona via jobs (`ProcessAiReview`, `ProcessMinutaDraft`) com 3 retentativas e backoff de 30/60/120s.

## Rodando localmente

**Pré-requisitos:** PHP 8.3, Composer, Node 20+, PostgreSQL (ou Docker)

```bash
# Dependências
composer install
npm install

# Configuração
cp .env.example .env
php artisan key:generate

# Edite o .env com suas credenciais de banco e API keys

# Banco
php artisan migrate

# Assets
npm run build

# Servidor
php artisan serve
php artisan queue:work  # necessário para processamento de IA
```

**Variáveis obrigatórias no `.env`:**

```env
DB_CONNECTION=pgsql
DB_HOST=...
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

ANTHROPIC_API_KEY=sk-ant-...
AI_PROVIDER=anthropic   # ou "mock" para desenvolvimento sem API

SUPABASE_URL=...
SUPABASE_SERVICE_ROLE_KEY=...
```

## Estrutura relevante

```
app/
  Services/AnthropicService.php   # cliente Anthropic + extração PDF/DOCX
  Jobs/ProcessAiReview.php        # análise assíncrona de documentos
  Jobs/ProcessMinutaDraft.php     # geração assíncrona de minutas
  Http/Middleware/
    EnsureOrganizationAccess.php  # isolamento multi-tenant
  Livewire/CasoChat.php           # chat em tempo real por caso
config/
  ai_prompts.php                  # prompts jurídicos padrão
  jusai.php                       # configurações do produto
```

## Observações

- Todo conteúdo gerado pela IA é marcado como `requires_human_review = true` por padrão.
- Os prompts seguem as diretrizes da OAB e citam exclusivamente fontes presentes nos documentos fornecidos — sem fabricar leis ou jurisprudência.
- Rate limit de 30 requisições/hora por usuário nos endpoints de IA.
