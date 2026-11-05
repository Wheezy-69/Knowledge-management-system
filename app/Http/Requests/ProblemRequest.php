<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProblemRequest extends FormRequest
{
    /** This MVP is intentionally a single-user local workspace. */
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:50000'],
            'programming_language' => ['required', 'string', 'max:40'],
            'tags' => ['nullable', 'array', 'max:12'],
            'tags.*' => ['required', 'string', 'max:40', 'distinct:ignore_case'],
            'corrected_solution' => ['nullable', 'string', 'max:100000'],
            'explanation' => ['nullable', 'string', 'max:50000'],
        ];
    }
}
