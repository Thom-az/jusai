@php
    $isMobile = $mobile ?? false;
    $sidebarId = $sidebarId ?? ($isMobile ? 'mobileSidebar' : 'desktopSidebar');
    $sidebarClasses = $isMobile
        ? 'offcanvas offcanvas-start shell-offcanvas d-lg-none'
        : 'shell-sidebar d-none d-lg-block';
@endphp

<aside
    class="{{ $sidebarClasses }}"
    tabindex="-1"
    @if($isMobile)
        id="{{ $sidebarId }}"
        aria-labelledby="mobileSidebarLabel"
    @endif
>
    <div class="{{ $isMobile ? 'offcanvas-body p-0' : '' }}">
        <div class="sidebar-card d-flex flex-column h-100">
            @if (! $isMobile)
                <button
                    type="button"
                    class="btn sidebar-toggle-button"
                    data-sidebar-toggle
                    aria-label="Recolher menu"
                    data-bs-toggle="tooltip"
                    data-bs-placement="right"
                    data-bs-title="Expandir menu"
                >
                    <i class="bi bi-chevron-left" data-sidebar-toggle-icon aria-hidden="true"></i>
                </button>
            @endif

            <div class="sidebar-header d-flex align-items-start gap-3">
                <a href="{{ route('dashboard') }}" class="sidebar-brand d-flex align-items-center gap-3">
                    <span class="sidebar-brand-copy">
                        <span class="d-block fw-semibold fs-5">{{ config('jusai.brand.name') }}</span>
                        <span class="small text-secondary">{{ config('jusai.brand.tagline') }}</span>
                    </span>
                </a>

                @if($isMobile)
                    <button type="button" class="btn-close mt-1" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
                @endif
            </div>

            <div class="sidebar-scroll">
                @foreach ($shellNavigation as $section)
                    <div class="sidebar-section mb-4">
                        <div class="sidebar-section-label mb-2">{{ $section['label'] }}</div>
                        @include('layouts.partials.sidebar-nav', ['items' => $section['items']])
                    </div>
                @endforeach
            </div>

            <div class="sidebar-footer d-flex align-items-center gap-3">
                <div class="avatar-chip flex-shrink-0">{{ $shellUser['initials'] }}</div>
                <div class="sidebar-meta-copy flex-grow-1 min-width-0">
                    <div class="sidebar-footer-name fw-semibold">{{ $shellUser['name'] }}</div>
                    <div class="sidebar-footer-role">{{ $shellUser['role'] }}</div>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 text-secondary" style="font-size: 0.72rem; text-decoration: none; line-height: 1.2;">
                            <i class="bi bi-box-arrow-right me-1"></i>Sair
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>
