<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProblemSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:200'],
            'vault_id' => ['nullable', 'integer', 'exists:vaults,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'language' => ['nullable', 'string', 'max:40'],
            'tag_ids' => ['nullable', 'array', 'max:12'],
            'tag_ids.*' => ['integer', 'distinct', 'exists:tags,id']];
    }
}
