@php
    $topbarAction = null;
    if (request()->routeIs('cases.index')) {
        $topbarAction = ['type' => 'modal', 'target' => '#modalNovoCaso', 'icon' => 'bi-folder-plus', 'label' => 'Novo caso'];
    } elseif (request()->is('casos*')) {
        $topbarAction = ['type' => 'link', 'href' => route('cases.create'), 'icon' => 'bi-folder-plus', 'label' => 'Novo caso'];
    } elseif (request()->routeIs('documents.index')) {
        $topbarAction = ['type' => 'modal', 'target' => '#modalEnviarDoc', 'icon' => 'bi-cloud-arrow-up', 'label' => 'Enviar documento'];
    } elseif (request()->is('documentos*')) {
        $topbarAction = ['type' => 'link', 'href' => route('documents.create'), 'icon' => 'bi-cloud-arrow-up', 'label' => 'Enviar documento'];
    } elseif (request()->is('revisor*')) {
        $topbarAction = ['type' => 'link', 'href' => route('review.index'), 'icon' => 'bi-cpu', 'label' => 'Nova análise'];
    } elseif (request()->is('chamados*')) {
        $topbarAction = ['type' => 'modal', 'target' => '#modalNovoChamado', 'icon' => 'bi-headset', 'label' => 'Novo chamado'];
    }
@endphp

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
            @if ($topbarAction)
                @if ($topbarAction['type'] === 'link')
                    <a href="{{ $topbarAction['href'] }}" wire:navigate class="btn btn-primary rounded-pill px-3 px-lg-4">
                        <i class="bi {{ $topbarAction['icon'] }} me-2"></i>{{ $topbarAction['label'] }}
                    </a>
                @else
                    <button type="button" class="btn btn-primary rounded-pill px-3 px-lg-4"
                            data-bs-toggle="modal" data-bs-target="{{ $topbarAction['target'] }}">
                        <i class="bi {{ $topbarAction['icon'] }} me-2"></i>{{ $topbarAction['label'] }}
                    </button>
                @endif
            @endif

            <div class="dropdown" id="notifDropdown">
                <button class="btn shell-icon-button position-relative"
                        type="button"
                        id="notifToggle"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="outside"
                        aria-expanded="false"
                        aria-label="Notificações">
                    <i class="bi bi-bell"></i>
                    <span id="notifBadge"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:0.6rem;padding:0.2em 0.4em;display:none;">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0"
                     id="notifPanel"
                     style="width:320px;border-radius:1rem;margin-top:0.5rem;overflow:hidden;">
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                        <span class="fw-semibold small">Notificações</span>
                        <button type="button"
                                id="notifMarkAll"
                                class="btn btn-link btn-sm p-0 text-secondary text-decoration-none"
                                style="font-size:0.75rem;">
                            Marcar todas como lidas
                        </button>
                    </div>
                    <div id="notifList" style="max-height:340px;overflow-y:auto;">
                        <div class="text-center text-secondary small py-4" id="notifEmpty">
                            <i class="bi bi-bell-slash d-block fs-4 mb-1"></i>
                            Nenhuma notificação
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn shell-icon-button" type="button" id="themeToggle" aria-label="Mudar para tema escuro">
                <i class="bi bi-moon" id="themeToggleIcon"></i>
            </button>

            <div class="dropdown">
                <button class="btn p-0 border-0 bg-transparent" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu do usuário">
                    <div class="avatar-chip" style="width:2.4rem;height:2.4rem;font-size:0.8rem;cursor:pointer;">{{ $shellUser['initials'] }}</div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width:210px;border-radius:1rem;margin-top:0.5rem;">
                    <li class="px-3 pt-3 pb-2">
                        <div class="fw-semibold small">{{ $shellUser['name'] }}</div>
                        <div class="text-secondary" style="font-size:0.75rem;">{{ $shellUser['role'] }}</div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item rounded-2 py-2" wire:navigate href="{{ route('profile.edit') }}">
                            <i class="bi bi-person me-2 text-secondary"></i>Meu perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item rounded-2 py-2" wire:navigate href="{{ route('settings.index') }}">
                            <i class="bi bi-gear me-2 text-secondary"></i>Configurações
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item rounded-2 py-2 text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Sair
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
