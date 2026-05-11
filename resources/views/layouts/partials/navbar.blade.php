<header class="topbar-wrap px-3 px-lg-4 pt-3 pt-lg-4">
    <div class="topbar d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            <button
                class="btn shell-icon-button d-lg-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileSidebar"
                aria-controls="mobileSidebar"
            >
                <i class="bi bi-list fs-5"></i>
            </button>

            @include('layouts.partials.sidebar', ['mobile' => true, 'sidebarId' => 'mobileSidebar'])

            <div class="flex-grow-1 topbar-search-wrap" style="max-width: 540px;">
                <div class="search-shell">
                    <i class="bi bi-search text-secondary"></i>
                    <input type="text" placeholder="Busca global por casos, clientes e documentos" readonly>
                    <span class="badge text-bg-light border">Em breve</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <a href="{{ route('cases.create') }}" class="btn btn-primary rounded-pill px-3 px-lg-4">
                <i class="bi bi-plus-circle me-2"></i>Novo caso
            </a>

            <button class="btn shell-icon-button position-relative" type="button" data-disabled-action="Central de notificacoes sera conectada na proxima etapa.">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>

            <div class="d-flex align-items-center gap-2 ps-lg-1">
                <div class="avatar-chip">{{ $shellUser['initials'] }}</div>
                <div class="d-none d-sm-block">
                    <div class="fw-semibold">{{ $shellUser['name'] }}</div>
                    <div class="small text-secondary">{{ $shellUser['role'] }}</div>
                </div>
            </div>
        </div>
    </div>
</header>
