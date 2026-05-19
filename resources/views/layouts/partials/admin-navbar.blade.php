<header class="topbar-wrap px-3 px-lg-4 pt-3 pt-lg-4">
    <div class="topbar d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            <button
                class="btn shell-icon-button d-lg-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#mobileAdminSidebar"
                aria-controls="mobileAdminSidebar"
            >
                <i class="bi bi-list fs-5"></i>
            </button>

            @include('layouts.partials.admin-sidebar', ['mobile' => true, 'sidebarId' => 'mobileAdminSidebar'])

            <span class="badge text-bg-danger px-3 py-2 rounded-pill">
                <i class="bi bi-shield-lock-fill me-1"></i>Painel Admin
            </span>
        </div>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-2"></i>Voltar ao app
            </a>

            <button class="btn shell-icon-button" type="button" id="themeToggle" aria-label="Mudar para tema escuro">
                <i class="bi bi-moon" id="themeToggleIcon"></i>
            </button>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn shell-icon-button" aria-label="Sair">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</header>
