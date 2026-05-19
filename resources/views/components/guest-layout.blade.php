<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Entrar') | {{ config('jusai.brand.name') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <script>(function(){document.documentElement.setAttribute('data-theme',localStorage.getItem('jusai.theme')||'light');})()</script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="shell-body d-flex align-items-center justify-content-center min-vh-100">
        <div class="auth-card">
            <div class="auth-brand mb-4 text-center">
                <span class="fw-bold fs-4">{{ config('jusai.brand.name') }}</span>
                <p class="text-secondary small mb-0">{{ config('jusai.brand.tagline') }}</p>
            </div>
            {{ $slot }}
        </div>
    </body>
</html>
