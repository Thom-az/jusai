@extends('layouts.app')

@section('title', 'Assistente Jurídico')

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h2 class="fw-semibold mb-1">Assistente Jurídico</h2>
                <p class="text-secondary mb-0 small">Converse com a IA sobre qualquer caso do escritório.</p>
            </div>
        </div>

        <div class="row g-4">
            {{-- Iniciar nova conversa --}}
            <div class="col-lg-4">
                <div class="surface-card p-4">
                    <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle me-2 text-primary"></i>Nova conversa</h6>
                    <p class="text-secondary small mb-3">Selecione um caso para começar a conversar com o assistente.</p>
                    @if ($cases->isEmpty())
                        <div class="text-secondary small">Nenhum caso ativo no momento.</div>
                    @else
                        <div class="d-flex flex-column gap-2">
                            @foreach ($cases as $case)
                                <a href="{{ route('cases.chat', $case) }}" wire:navigate
                                   class="btn btn-outline-secondary text-start rounded-3 px-3 py-2 d-flex align-items-center gap-2">
                                    <i class="bi bi-briefcase text-primary flex-shrink-0"></i>
                                    <span class="small text-truncate">{{ $case->title }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Conversas recentes --}}
            <div class="col-lg-8">
                <div class="surface-card p-0">
                    <div class="px-4 py-3 border-bottom">
                        <h6 class="fw-semibold mb-0">Conversas recentes</h6>
                    </div>

                    @if ($conversations->isEmpty())
                        <div class="p-5 text-center text-secondary">
                            <i class="bi bi-chat-dots fs-2 d-block mb-2 opacity-50"></i>
                            <div class="small">Nenhuma conversa ainda. Selecione um caso ao lado para começar.</div>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($conversations as $conv)
                                @php
                                    $lastMessage = $conv->messages->first();
                                @endphp
                                <a href="{{ route('cases.chat', $conv->legalCase) }}" wire:navigate
                                   class="list-group-item list-group-item-action px-4 py-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:2.25rem;height:2.25rem;">
                                            <i class="bi bi-cpu text-white" style="font-size:.8rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="fw-medium text-truncate">{{ $conv->legalCase?->title ?? '—' }}</div>
                                            @if ($lastMessage)
                                                <div class="text-secondary small text-truncate">
                                                    {{ $lastMessage->role === 'user' ? 'Você: ' : 'IA: ' }}{{ Str::limit($lastMessage->content, 80) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-secondary small flex-shrink-0">
                                            {{ $conv->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
