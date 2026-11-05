<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VaultRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:10000'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_cover' => ['sometimes', 'boolean'],
        ];
    }
}
