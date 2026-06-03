@props(['current' => null])

@php
    $activeSection = $current ?? str_replace('settings.', '', request()->route()->getName() ?? '');

    $isActive = fn (string $section): bool => $activeSection === $section;

    $navGroups = [
        [
            'label' => 'Conta',
            'items' => [
                ['label' => 'Perfil',       'icon' => 'bi-person-circle', 'route' => 'settings.perfil'],
                ['label' => 'Preferências', 'icon' => 'bi-sliders2',      'route' => 'settings.preferencias'],
            ],
        ],
        [
            'label' => 'Escritório',
            'items' => [
                ['label' => 'Dados do escritório', 'icon' => 'bi-building',      'route' => 'settings.escritorio', 'can' => 'view-firm'],
                ['label' => 'Equipe',               'icon' => 'bi-people',        'route' => 'settings.equipe',     'can' => 'view-team'],
            ],
        ],
        [
            'label' => 'Sistema',
            'items' => [
                ['label' => 'Segurança',           'icon' => 'bi-shield-lock',  'route' => 'settings.seguranca'],
                ['label' => 'Plano e faturamento', 'icon' => 'bi-credit-card',  'route' => 'settings.plano', 'can' => 'view-billing'],
            ],
        ],
    ];
@endphp

<aside class="settings-sidebar" aria-label="Navegação de configurações">

    <div class="settings-sidebar-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-gear text-secondary" style="font-size: 1rem;" aria-hidden="true"></i>
            <span class="fw-semibold" style="font-size: 0.9rem;">Configurações</span>
        </div>
    </div>

    <nav class="settings-nav" aria-label="Seções de configurações">
        @foreach ($navGroups as $group)
            @php
                $visibleItems = collect($group['items'])->filter(
                    fn ($item) => empty($item['can']) || auth()->user()?->can($item['can'])
                );
            @endphp

            @if ($visibleItems->isNotEmpty())
                <div class="settings-nav-group">
                    <div class="settings-nav-group-label">{{ $group['label'] }}</div>

                    @foreach ($visibleItems as $item)
                        @php $active = $isActive(str_replace('settings.', '', $item['route'])); @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            wire:navigate.hover
                            class="settings-nav-link {{ $active ? 'active' : '' }}"
                            @if($active) aria-current="page" @endif
                        >
                            <span class="settings-nav-link-icon">
                                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                            </span>
                            <span class="settings-nav-link-label">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </nav>

</aside>
