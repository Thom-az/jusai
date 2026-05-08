<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Dashboard') | {{ config('jusai.brand.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="app-shell d-flex">
            @include('layouts.partials.sidebar', ['mobile' => false])

            <div class="content-area flex-grow-1">
                @include('layouts.partials.navbar')

                <main class="px-3 px-lg-4 py-4 py-lg-4">
                    @yield('content')
                </main>
            </div>
        </div>

        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
            <div id="appToast" class="toast border-0 shadow-lg" role="status" aria-live="polite" aria-atomic="true">
                <div class="toast-header">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    <strong class="me-auto">{{ config('jusai.brand.name') }}</strong>
                    <small>Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
                <div class="toast-body" id="appToastBody">
                    Este recurso sera entregue na proxima etapa.
                </div>
            </div>
        </div>
    </body>
</html>
