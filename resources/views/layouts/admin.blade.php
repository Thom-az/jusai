<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Admin') | {{ config('jusai.brand.name') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <script>(function(){var d=document.documentElement;d.setAttribute('data-theme',localStorage.getItem('jusai.theme')||'light');d.setAttribute('data-sidebar-state',localStorage.getItem('jusai.sidebar.state')||'expanded');})()</script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="shell-body">
        <div class="app-shell">
            @include('layouts.partials.admin-sidebar', ['mobile' => false, 'sidebarId' => 'desktopAdminSidebar'])

            <div class="content-area">
                @include('layouts.partials.admin-navbar')

                <main class="content-main px-3 px-lg-4 pb-4 pb-lg-5">
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
