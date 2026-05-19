<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLegalCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['sometimes', 'required', 'string', 'max:255'],
            'client_name'    => ['sometimes', 'required', 'string', 'max:255'],
            'client_email'   => ['nullable', 'email', 'max:255'],
            'client_phone'   => ['nullable', 'string', 'max:50'],
            'area'           => ['nullable', Rule::in(['civil', 'criminal', 'trabalhista', 'tributario', 'empresarial', 'familia', 'imobiliario', 'previdenciario', 'administrativo', 'outro'])],
            'status'         => ['nullable', Rule::in(['triagem', 'em_andamento', 'aguardando_cliente', 'aguardando_prazo', 'em_recurso', 'encerrado', 'arquivado'])],
            'risk_level'     => ['nullable', Rule::in(['baixo', 'medio', 'alto', 'critico'])],
            'description'    => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'assigned_to'    => ['nullable', 'integer', 'exists:users,id'],
            'opened_at'      => ['nullable', 'date'],
        ];
    }
}
