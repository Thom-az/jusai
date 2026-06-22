<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'file'          => ['required', 'file', 'mimes:pdf,docx,doc,txt', 'max:1048576'],
            'legal_case_id' => ['nullable', 'uuid', 'exists:legal_cases,id'],
        ];
    }
}
