@extends('layouts.app')

@section('title', 'Notificações')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Notificações</h2>
                <p class="text-secondary mb-0 small">Histórico de todas as notificações recebidas</p>
            </div>
            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.readAll') }}" data-no-page-loader>
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
                        <i class="bi bi-check2-all me-1"></i>Marcar todas como lidas
                    </button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <div class="surface-card p-5 text-center text-secondary">
                <i class="bi bi-bell-slash fs-1 d-block mb-3 opacity-50"></i>
                <div class="fw-semibold mb-1">Nenhuma notificação</div>
                <div class="small">Quando houver atividade na plataforma, você verá aqui.</div>
            </div>
        @else
            <div class="surface-card p-0">
                @foreach ($notifications as $notif)
                    @php
                        $isUnread = is_null($notif->read_at);
                        $type     = $notif->data['type'] ?? 'info';
                        $title    = $notif->data['title'] ?? 'Notificação';
                        $message  = $notif->data['message'] ?? '';
                        $url      = $notif->data['url'] ?? null;

                        [$icon, $iconColor] = match ($type) {
                            'document_analysis_complete' => ['bi-file-earmark-check-fill', 'text-success'],
                            'document_analysis_error'    => ['bi-file-earmark-x-fill', 'text-danger'],
                            default                      => ['bi-bell-fill', 'text-primary'],
                        };
                    @endphp
                    <div class="d-flex align-items-start gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }} {{ $isUnread ? 'notif-unread' : '' }}">
                        <div class="flex-shrink-0 mt-1">
                            <i class="bi {{ $icon }} {{ $iconColor }}" style="font-size:1.15rem"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fw-semibold small">{{ $title }}</span>
                                @if ($isUnread)
                                    <span class="badge text-bg-primary rounded-pill" style="font-size:.65rem;padding:.2rem .5rem">Nova</span>
                                @endif
                            </div>
                            <div class="text-secondary small">{{ $message }}</div>
                            <div class="text-secondary mt-1" style="font-size:.75rem">
                                {{ $notif->created_at->format('d/m/Y \à\s H:i') }}
                                @if (!$isUnread)
                                    &bull; Lida {{ $notif->read_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            @if ($url)
                                <a href="{{ $url }}" wire:navigate class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size:.8rem">
                                    Ver
                                </a>
                            @endif
                            @if ($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notif->id) }}" data-no-page-loader>
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size:.8rem" title="Marcar como lida">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
.notif-unread {
    background: rgba(37, 99, 235, 0.03);
    border-left: 3px solid var(--jusai-action) !important;
}
[data-theme="dark"] .notif-unread {
    background: rgba(96, 165, 250, 0.05);
    border-left-color: #60a5fa !important;
}
</style>
@endpush
