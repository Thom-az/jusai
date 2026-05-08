@php
    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'bi-grid', 'route' => route('dashboard'), 'pattern' => 'dashboard'],
        ['label' => 'Casos', 'icon' => 'bi-briefcase', 'route' => route('cases.index'), 'pattern' => 'casos*'],
        ['label' => 'Documentos', 'icon' => 'bi-file-earmark-text', 'route' => route('documents.index'), 'pattern' => 'documentos*'],
        ['label' => 'Minutas', 'icon' => 'bi-journal-richtext', 'route' => route('drafts.index'), 'pattern' => 'minutas*'],
        ['label' => 'Revisor Juridico', 'icon' => 'bi-shield-check', 'route' => route('review.index'), 'pattern' => 'revisor*'],
        ['label' => 'Configuracoes', 'icon' => 'bi-sliders', 'route' => route('settings.index'), 'pattern' => 'configuracoes*'],
    ];

    $sidebarClasses = $mobile ?? false
        ? 'offcanvas offcanvas-start sidebar-desktop d-lg-none'
        : 'sidebar-desktop d-none d-lg-flex flex-column position-sticky top-0';
@endphp

<aside
    class="{{ $sidebarClasses }}"
    tabindex="-1"
    @if($mobile ?? false)
        id="mobileSidebar"
        aria-labelledby="mobileSidebarLabel"
    @endif
>
    <div class="p-3 p-xl-4 d-flex flex-column h-100">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <a href="{{ route('dashboard') }}" class="sidebar-brand d-flex align-items-center gap-3">
                <span class="sidebar-brand-mark">
                    <i class="bi bi-balance-scale fs-5"></i>
                </span>
                <span>
                    <span class="d-block fw-semibold fs-5">{{ config('jusai.brand.name') }}</span>
                    <span class="small text-white-50">{{ config('jusai.brand.tagline') }}</span>
                </span>
            </a>

            @if($mobile ?? false)
                <button type="button" class="btn-close btn-close-white mt-1" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
            @endif
        </div>

        <div class="sidebar-meta-card p-3 mb-4">
            <div class="small text-uppercase text-white-50 fw-semibold mb-2">MVP juridico</div>
            <div class="fw-semibold text-white mb-2">Plataforma em estruturacao</div>
            <p class="small mb-0 text-white-50">
                Fluxos de IA, auditoria e documentos serao conectados em etapas, com revisao humana obrigatoria.
            </p>
        </div>

        <div class="sidebar-section-label mb-2">Navegacao</div>
        <nav class="d-grid gap-2">
            @foreach ($navItems as $item)
                <a
                    href="{{ $item['route'] }}"
                    class="sidebar-link {{ request()->is($item['pattern']) ? 'active' : '' }}"
                >
                    <span class="sidebar-link-icon">
                        <i class="bi {{ $item['icon'] }}"></i>
                    </span>
                    <span class="fw-medium">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="mt-auto pt-4">
            <div class="sidebar-section-label mb-2">Governanca</div>
            <div class="sidebar-meta-card p-3">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-shield-lock fs-4 text-warning"></i>
                    <div>
                        <div class="fw-semibold text-white mb-1">Revisao humana</div>
                        <p class="small text-white-50 mb-0">
                            {{ config('jusai.ai.review_notice') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
