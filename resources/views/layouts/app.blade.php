<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Dashboard') | {{ config('jusai.brand.name') }}</title>
        <script>
            (function () {
                const root = document.documentElement;
                const navigationKey = 'jusai.pending.navigation';
                let pendingNavigation = null;

                root.setAttribute('data-theme', localStorage.getItem('jusai.theme') || 'light');
                root.setAttribute('data-sidebar-state', localStorage.getItem('jusai.sidebar.state') || 'expanded');

                try {
                    pendingNavigation = sessionStorage.getItem(navigationKey);

                    if (pendingNavigation) {
                        const parsedNavigation = JSON.parse(pendingNavigation);

                        if (!parsedNavigation?.startedAt || (Date.now() - parsedNavigation.startedAt) > 15000) {
                            sessionStorage.removeItem(navigationKey);
                            pendingNavigation = null;
                        }
                    }
                } catch (error) {
                    sessionStorage.removeItem(navigationKey);
                    pendingNavigation = null;
                }

                if (pendingNavigation) {
                    root.setAttribute('data-page-loading', 'pending');
                }
            }());
        </script>
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="shell-body" data-shell-context="app">
        <div class="app-shell" data-sidebar-state="expanded">
            @include('layouts.partials.sidebar', ['mobile' => false, 'sidebarId' => 'desktopSidebar'])

            <div class="content-area">
                @include('layouts.partials.navbar')

                <main class="content-main px-3 px-lg-4 pb-4 pb-lg-5">
                    @yield('content')
                </main>
            </div>
        </div>

        {{-- Toast global — Bootstrap nativo, canto superior direito --}}
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090">
            <div id="globalToast"
                 class="toast border-0 shadow"
                 role="alert" aria-live="assertive" aria-atomic="true"
                 data-bs-delay="4500" data-bs-autohide="true">
                <div id="globalToastInner" class="d-flex align-items-center gap-2 p-3 rounded">
                    <i id="globalToastIcon" class="bi flex-shrink-0 fs-6"></i>
                    <span id="globalToastMessage" class="small fw-semibold flex-grow-1"></span>
                    <button type="button" class="btn-close btn-close-sm flex-shrink-0 ms-1"
                            data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
            </div>
        </div>

        @stack('scripts')
        @livewireScripts
    </body>
</html>
