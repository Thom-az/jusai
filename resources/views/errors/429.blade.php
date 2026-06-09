<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Limite atingido | {{ config('jusai.brand.name', 'JusAI') }}</title>
    <script>
        (function () {
            document.documentElement.setAttribute(
                'data-theme',
                localStorage.getItem('jusai.theme') || 'light'
            );
        }());
    </script>
    @vite(['resources/css/app.css'])
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid var(--jusai-border, #d7dce5);
            border-radius: 1.5rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.10);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }

        .error-icon-wrap {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(217, 119, 6, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .error-code {
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #d97706;
            margin-bottom: 0.5rem;
        }

        .error-title {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--jusai-graphite, #1f2937);
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .error-description {
            color: var(--jusai-muted, #6b7280);
            font-size: 0.9375rem;
            line-height: 1.65;
            margin-bottom: 2rem;
        }

        .error-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        [data-theme="dark"] .error-page {
            background: linear-gradient(135deg, rgba(11, 31, 58, 0.95) 0%, #0d0d0d 100%);
        }

        [data-theme="dark"] .error-card {
            background: #1a1a1a;
            border-color: rgba(255, 255, 255, 0.07);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
        }

        [data-theme="dark"] .error-title {
            color: rgba(255, 255, 255, 0.92);
        }

        [data-theme="dark"] .error-description {
            color: rgba(255, 255, 255, 0.52);
        }

        [data-theme="dark"] .error-icon-wrap {
            background: rgba(217, 119, 6, 0.15);
        }
    </style>
</head>
<body class="shell-body">

<div class="error-page">
    <div class="error-card">

        <div class="error-icon-wrap">
            <i class="bi bi-hourglass-split" style="font-size: 1.75rem; color: #d97706;"></i>
        </div>

        <div class="error-code">Erro 429 — Muitas requisições</div>

        <h1 class="error-title">Limite de IA atingido</h1>

        <p class="error-description">
            Você atingiu o limite de análises de IA por hora (30 por hora).<br>
            Aguarde alguns minutos e tente novamente.
        </p>

        <div class="error-actions">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
               class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-house me-1"></i> Início
            </a>
        </div>

    </div>
</div>

</body>
</html>
