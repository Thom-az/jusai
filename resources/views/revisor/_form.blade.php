{{-- Partial do formulário de nova análise. Usado no layout solo e no layout 2 colunas. --}}
<form method="POST" action="{{ route('review.store') }}" id="reviewForm">
    @csrf

    {{-- Cards de tipo de análise --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">Tipo de análise <span class="text-danger">*</span></label>
        @error('type')
            <div class="text-danger small mb-2">{{ $message }}</div>
        @enderror
        <div class="row g-2">
            @foreach ([
                ['value' => 'resumo_caso',       'icon' => 'bi-file-text',          'label' => 'Resumo do caso',       'desc' => 'Visão geral estruturada'],
                ['value' => 'analise_documento',  'icon' => 'bi-file-earmark-search','label' => 'Análise de documento', 'desc' => 'Extrai cláusulas e riscos'],
                ['value' => 'revisao_minuta',     'icon' => 'bi-journal-check',      'label' => 'Revisão de minuta',    'desc' => 'Aponta falhas e lacunas'],
                ['value' => 'pesquisa_juridica',  'icon' => 'bi-search',             'label' => 'Pesquisa jurídica',    'desc' => 'Responde pergunta jurídica'],
            ] as $t)
            <div class="col-6">
                <label class="analysis-type-card w-100"
                       :class="{ 'active': type === '{{ $t['value'] }}' }">
                    <input type="radio" name="type" value="{{ $t['value'] }}"
                           class="visually-hidden"
                           x-model="type"
                           @if(old('type') === $t['value']) checked @endif>
                    <i class="bi {{ $t['icon'] }}"></i>
                    <span class="d-block fw-semibold mt-2" style="font-size:.85rem">{{ $t['label'] }}</span>
                    <span class="d-block text-secondary" style="font-size:.72rem;line-height:1.4">{{ $t['desc'] }}</span>
                </label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="mb-3">
        <label for="legal_case_id" class="form-label fw-semibold">Caso <span class="text-danger">*</span></label>
        <select id="legal_case_id" name="legal_case_id"
                class="form-select @error('legal_case_id') is-invalid @enderror">
            <option value="">Selecionar caso...</option>
            @foreach ($cases as $case)
                <option value="{{ $case->id }}" @selected(old('legal_case_id', request('case_id')) == $case->id)>
                    {{ $case->title }}
                </option>
            @endforeach
        </select>
        @error('legal_case_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3" x-show="type === 'analise_documento' || type === 'resumo_caso'" x-cloak>
        <label for="document_id" class="form-label fw-semibold">Documento</label>
        <select id="document_id" name="document_id"
                class="form-select @error('document_id') is-invalid @enderror">
            <option value="">Selecionar documento...</option>
            @foreach ($documents as $doc)
                <option value="{{ $doc->id }}" @selected(old('document_id') == $doc->id)>{{ $doc->title }}</option>
            @endforeach
        </select>
        @error('document_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3" x-show="type === 'revisao_minuta'" x-cloak>
        <label for="draft_id" class="form-label fw-semibold">Minuta</label>
        <select id="draft_id" name="draft_id"
                class="form-select @error('draft_id') is-invalid @enderror">
            <option value="">Selecionar minuta...</option>
            @foreach ($drafts as $draft)
                <option value="{{ $draft->id }}" @selected(old('draft_id') == $draft->id)>{{ $draft->title }}</option>
            @endforeach
        </select>
        @error('draft_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3" x-show="type === 'pesquisa_juridica'" x-cloak>
        <label for="question" class="form-label fw-semibold">Pergunta jurídica <span class="text-danger">*</span></label>
        <textarea id="question" name="question" rows="3"
                  class="form-control @error('question') is-invalid @enderror"
                  placeholder="Ex: Quais os riscos de rescisão sem justa causa neste contrato?">{{ old('question') }}</textarea>
        @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <x-ai-disclaimer class="mb-4" />

    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
        <i class="bi bi-cpu me-2"></i>Iniciar análise
    </button>
</form>
