@extends('layouts.app')

@section('title', 'Novo Caso')

@push('styles')
    @vite(['resources/css/modules/casos.css'])
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('cases.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i>Voltar
            </a>
            <div>
                <h2 class="fw-semibold mb-0">Novo Caso</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('cases.store') }}">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="surface-card p-4 mb-4">
                        <h5 class="fw-semibold mb-4">Dados do caso</h5>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Titulo do caso <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Ex: Acao de cobranca - Empresa XYZ">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="area" class="form-label fw-semibold">Area juridica <span class="text-danger">*</span></label>
                                <select id="area" name="area" class="form-select @error('area') is-invalid @enderror">
                                    <option value="">Selecionar...</option>
                                    @foreach (['civil' => 'Civil', 'criminal' => 'Criminal', 'trabalhista' => 'Trabalhista', 'tributario' => 'Tributario', 'empresarial' => 'Empresarial', 'familia' => 'Familia', 'imobiliario' => 'Imobiliario', 'previdenciario' => 'Previdenciario', 'administrativo' => 'Administrativo', 'outro' => 'Outro'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('area') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach (['triagem' => 'Triagem', 'em_andamento' => 'Em andamento', 'aguardando_cliente' => 'Aguardando cliente', 'aguardando_prazo' => 'Aguardando prazo'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status', 'triagem') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-sm-6">
                                <label for="risk_level" class="form-label fw-semibold">Nivel de risco</label>
                                <select id="risk_level" name="risk_level" class="form-select @error('risk_level') is-invalid @enderror">
                                    <option value="">Nao definido</option>
                                    @foreach (['baixo' => 'Baixo', 'medio' => 'Medio', 'alto' => 'Alto', 'critico' => 'Critico'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('risk_level') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('risk_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-6">
                                <label for="opened_at" class="form-label fw-semibold">Data de abertura <span class="text-danger">*</span></label>
                                <input type="date" id="opened_at" name="opened_at" class="form-control @error('opened_at') is-invalid @enderror" value="{{ old('opened_at', now()->format('Y-m-d')) }}">
                                @error('opened_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="assigned_to" class="form-label fw-semibold">Responsavel</label>
                            <select id="assigned_to" name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                <option value="">Nao atribuido</option>
                                @foreach ($lawyers as $lawyer)
                                    <option value="{{ $lawyer->id }}" @selected(old('assigned_to') == $lawyer->id)>{{ $lawyer->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mt-3">
                            <label for="description" class="form-label fw-semibold">Descricao</label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" placeholder="Contexto e historico do caso...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="surface-card p-4">
                        <h5 class="fw-semibold mb-4">Notas internas</h5>
                        <div>
                            <label for="internal_notes" class="form-label fw-semibold">Notas (visiveis apenas para a equipe)</label>
                            <textarea id="internal_notes" name="internal_notes" class="form-control @error('internal_notes') is-invalid @enderror" rows="3" placeholder="Estrategia, observacoes confidenciais...">{{ old('internal_notes') }}</textarea>
                            @error('internal_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="surface-card p-4 mb-4">
                        <h5 class="fw-semibold mb-4">Dados do cliente</h5>

                        <div class="mb-3">
                            <label for="client_name" class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                            <input type="text" id="client_name" name="client_name" class="form-control @error('client_name') is-invalid @enderror" value="{{ old('client_name') }}" placeholder="Nome completo ou razao social">
                            @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="client_email" class="form-label fw-semibold">E-mail</label>
                            <input type="email" id="client_email" name="client_email" class="form-control @error('client_email') is-invalid @enderror" value="{{ old('client_email') }}">
                            @error('client_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div>
                            <label for="client_phone" class="form-label fw-semibold">Telefone</label>
                            <input type="text" id="client_phone" name="client_phone" class="form-control @error('client_phone') is-invalid @enderror" value="{{ old('client_phone') }}" placeholder="(11) 90000-0000">
                            @error('client_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                        <i class="bi bi-folder-plus me-2"></i>Criar caso
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
