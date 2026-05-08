# JusAI

Base inicial do projeto em Laravel com infraestrutura Docker via Laravel Sail.

## Stack

- Laravel 13
- PHP 8.5 no runtime do Sail
- MySQL 8.4
- Redis
- Mailpit
- Vite para assets

## Estrutura criada

- `compose.yaml`: orquestra os containers da aplicação, banco, Redis e Mailpit
- `.env.example`: configuração padrão do ambiente local com Docker
- `vendor/bin/sail.bat`: ponto de entrada para subir e operar o ambiente no Windows

## Como subir o projeto

### Windows PowerShell

```powershell
copy .env.example .env
vendor\bin\sail.bat up -d
vendor\bin\sail.bat artisan migrate
vendor\bin\sail.bat npm install
vendor\bin\sail.bat npm run dev
```

### Linux / macOS

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

## Serviços padrão

- Aplicação: `http://localhost`
- Mailpit: `http://localhost:8025`
- MySQL: `127.0.0.1:3306`
- Redis: `127.0.0.1:6379`

## Comandos úteis

```powershell
vendor\bin\sail.bat artisan test
vendor\bin\sail.bat artisan make:model Nome -mcr
vendor\bin\sail.bat composer install
vendor\bin\sail.bat php --version
```

## Observações

- O projeto está configurado para rodar a aplicação em Docker com MySQL como banco principal.
- Os testes automatizados usam SQLite em memória para não depender do banco em container.
- Se o Docker Desktop ainda não estiver instalado ou ativo na máquina, instale e inicie antes de subir os containers.
