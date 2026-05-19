<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TriggerAiReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'          => ['required', Rule::in(['resumo_caso', 'analise_documento', 'revisao_minuta', 'pesquisa_juridica'])],
            'legal_case_id' => ['required', 'uuid', 'exists:legal_cases,id'],
            'document_id'   => ['nullable', 'uuid', 'exists:documents,id'],
            'draft_id'      => ['nullable', 'uuid', 'exists:drafts,id'],
            'question'      => ['nullable', 'string', 'max:2000', 'required_if:type,pesquisa_juridica'],
        ];
    }
}
