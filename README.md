# JusAI

Base inicial do projeto em Laravel com Docker para a aplicação e Supabase como banco principal.

## Stack

- Laravel 13
- PHP 8.5 no runtime do Sail
- Supabase Postgres como banco principal
- Vite para assets
- Bootstrap 5.3
- Bootstrap Icons

## Estrutura criada

- `compose.yaml`: sobe apenas a aplicação Laravel/Vite em Docker
- `.env.example`: modelo de ambiente local com Supabase e integrações futuras
- `vendor/bin/sail.bat`: ponto de entrada para subir e operar o ambiente no Windows

## Como subir o projeto

### Windows PowerShell

```powershell
copy .env.example .env
notepad .env
vendor\bin\sail.bat up -d
vendor\bin\sail.bat artisan migrate
vendor\bin\sail.bat npm install
vendor\bin\sail.bat npm run dev
```

### Linux / macOS

```bash
cp .env.example .env
nano .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

## Variáveis que você precisa preencher

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `SUPABASE_URL`
- `SUPABASE_ANON_KEY`
- `SUPABASE_SERVICE_ROLE_KEY`
- `SUPABASE_JWT_SECRET`

## Serviços padrão

- Aplicação: `http://localhost`
- Banco principal: Supabase Postgres externo
- IA: modo `mock` por padrão

## Comandos úteis

```powershell
vendor\bin\sail.bat artisan test
vendor\bin\sail.bat artisan make:model Nome -mcr
vendor\bin\sail.bat composer install
vendor\bin\sail.bat php --version
```

## Observações

- O projeto está configurado para rodar a aplicação em Docker e usar o Supabase como banco principal.
- Se o Supabase fornecer uma connection string, você pode preencher `DB_URL`; caso contrário, use `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD`.
- `DB_SSLMODE=require` já fica preparado para conexões seguras com o Postgres do Supabase.
- Os testes automatizados usam SQLite em memória para não depender do banco externo.
- Se o Docker Desktop ainda não estiver instalado ou ativo na máquina, instale e inicie antes de subir os containers.
