# JusAI — Guia de Desenvolvimento

> ⚠️ **Este arquivo é apenas para desenvolvimento local.**
> Nunca commitar credenciais reais. Nunca usar em produção.

---

## Usuários de teste (após `php artisan db:seed`)

### Super-Admin JusAI (acesso ao painel `/admin`)

| Campo  | Valor                  |
|--------|------------------------|
| Email  | `admin@jusai.com.br`   |
| Senha  | `password`             |
| Role   | `super_admin` (coluna) |

---

### Escritório Silva & Associados

Todos com senha: **`password`**

| Nome              | Email                                  | Role Spatie   | Permissões principais                                                |
|-------------------|----------------------------------------|---------------|----------------------------------------------------------------------|
| Dr. Carlos Silva  | `carlos@silva-associados.adv.br`       | `admin`       | Todas as permissões                                                  |
| Dra. Mariana F.   | `mariana@silva-associados.adv.br`      | `socio`       | view-firm, view-team, view-billing, manage-cases, use-ai-analysis    |
| Ana Beatriz Lima  | `ana@silva-associados.adv.br`          | `advogado`    | manage-cases, manage-documents, use-ai-analysis                      |
| Rafael Oliveira   | `rafael@silva-associados.adv.br`       | `estagiario`  | manage-cases (limitado), use-ai-analysis (com limite)                |
| Patrícia Souza    | `patricia@silva-associados.adv.br`     | `secretario`  | manage-cases (agenda/atribuição), view-team                          |
| Ricardo Alves     | `ricardo@silva-associados.adv.br`      | `financeiro`  | view-billing, manage-billing                                         |

---

## Roles e permissões (Spatie)

### Roles disponíveis

| Role        | Descrição                              |
|-------------|----------------------------------------|
| `admin`     | Administrador do escritório            |
| `socio`     | Sócio com amplo acesso                 |
| `advogado`  | Advogado operacional                   |
| `estagiario`| Estagiário com acesso limitado         |
| `secretario`| Secretária / administrativo            |
| `financeiro`| Responsável pelo financeiro            |

### Permissões granulares

| Permissão               | Descrição                                      |
|-------------------------|------------------------------------------------|
| `manage-firm`           | Editar dados do escritório                     |
| `view-firm`             | Ver dados do escritório                        |
| `manage-team`           | Gerenciar usuários da equipe                   |
| `view-team`             | Ver lista de usuários                          |
| `manage-billing`        | Gerenciar plano e faturamento                  |
| `view-billing`          | Ver plano e uso                                |
| `manage-security-policy`| Definir políticas de senha/segurança           |
| `manage-cases`          | Criar e editar casos                           |
| `view-all-cases`        | Ver todos os casos do escritório               |
| `manage-documents`      | Gerenciar documentos                           |
| `use-ai-analysis`       | Usar análise de IA                             |
| `manage-templates`      | Gerenciar templates de minutas                 |

---

## Comandos úteis

```bash
# Reset completo do banco + seed
php artisan migrate:fresh --seed

# Limpar cache de permissões (após mudanças no seeder)
php artisan cache:clear
php artisan permission:cache-reset

# Criar chave de app se necessário
php artisan key:generate

# Build dos assets
npm run build
# ou em desenvolvimento:
npm run dev
```

---

## Variáveis de ambiente necessárias (`.env`)

```env
APP_NAME=JusAI
APP_ENV=local
APP_KEY=  # gerado por php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# ou:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jusai
DB_USERNAME=root
DB_PASSWORD=

ANTHROPIC_API_KEY=sk-ant-...
SUPABASE_URL=https://xxx.supabase.co
SUPABASE_SERVICE_ROLE_KEY=...
```

---

## Middleware aliases

| Alias            | Uso                                                  |
|------------------|------------------------------------------------------|
| `role:super_admin` | Protege rotas do painel JusAI (coluna `role`)       |
| `org.access`     | Garante que o usuário pertence a um escritório ativo |
| `permission:manage-firm` | Protege rotas de configurações do escritório  |
| `spatie.role:admin` | Protege por role Spatie                           |

---

*Atualizado em: {{ date('Y-m-d') }}*
