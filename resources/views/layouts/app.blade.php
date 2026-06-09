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

        {{-- Modal global de visualização de arquivos --}}
        <div class="modal fade" id="modalFilePreview" tabindex="-1" aria-label="Visualizar arquivo" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header py-3">
                        <div class="min-width-0 flex-grow-1">
                            <h6 class="modal-title fw-semibold mb-0 text-truncate" id="previewFileTitle">Carregando…</h6>
                            <div class="text-secondary small text-truncate mt-1" id="previewFileSubtitle"></div>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-3 flex-shrink-0">
                            <a href="#" id="previewDownloadBtn" download
                               class="btn btn-sm btn-outline-secondary rounded-pill px-3" target="_blank">
                                <i class="bi bi-download me-1"></i>Baixar
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body p-3">
                        <div id="previewSpinner" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="text-secondary small mt-3">Carregando arquivo…</div>
                        </div>
                        <div id="previewBody" class="d-none"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal global de confirmação de exclusão --}}
        <div class="modal fade" id="modalConfirmDelete" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="confirmDeleteTitle" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-semibold" id="confirmDeleteTitle">Confirmar exclusão</h6>
                    </div>
                    <div class="modal-body pt-2 pb-3">
                        <p class="text-secondary small mb-0" id="confirmDeleteMessage">Esta ação é irreversível e não poderá ser desfeita.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" id="confirmDeleteBtn">
                            <i class="bi bi-trash me-1"></i>Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @vite(['resources/js/modules/file-preview.js'])
        @stack('scripts')
        @livewireScripts
    </body>
</html>
