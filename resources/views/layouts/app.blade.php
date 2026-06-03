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

        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
            <div id="appToast" class="toast border-0 shadow-lg" role="status" aria-live="polite" aria-atomic="true">
                <div class="toast-header">
                    <i class="bi bi-info-circle text-primary me-2"></i>
                    <strong class="me-auto">{{ config('jusai.brand.name') }}</strong>
                    <small>Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
                <div class="toast-body" id="appToastBody">
                    Este recurso será entregue na próxima etapa.
                </div>
            </div>
        </div>

        {{-- Toast global — canto superior direito --}}
        <div
            x-data="{
                show: false, message: '', type: 'success', _t: null,
                display(msg, t) {
                    this.message = msg; this.type = t || 'success'; this.show = true;
                    clearTimeout(this._t);
                    this._t = setTimeout(() => this.show = false, 4500);
                }
            }"
            x-on:app:toast.window="display($event.detail.message, $event.detail.type)"
            class="position-fixed"
            style="top: 1.25rem; right: 1.25rem; z-index: 1090; pointer-events: none;"
        >
            <div
                x-show="show"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="{
                    'd-flex': show,
                    'app-toast--success': type === 'success',
                    'app-toast--warning': type === 'warning',
                    'app-toast--danger':  type === 'danger',
                }"
                class="app-toast"
                style="display:none; pointer-events: auto;"
                role="alert"
            >
                <i class="bi flex-shrink-0" :class="{
                    'bi-check-circle-fill': type === 'success',
                    'bi-exclamation-triangle-fill': type === 'warning',
                    'bi-x-circle-fill': type === 'danger',
                }"></i>
                <span x-text="message" class="small fw-semibold flex-grow-1"></span>
                <button type="button" class="btn-close btn-close-sm ms-1 flex-shrink-0"
                        @click="show = false; clearTimeout(_t)" aria-label="Fechar"></button>
            </div>
        </div>

        @stack('scripts')
        @livewireScripts
    </body>
</html>
