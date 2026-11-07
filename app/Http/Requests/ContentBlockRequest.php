<?php

namespace App\Http\Requests;

use App\Models\ContentBlock;
use App\Models\Problem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $problem = $this->route('problem');
        $block = $this->route('block');

        return ['type' => ['required', 'in:text,code'], 'content' => ['required', 'string', 'max:100000'],
            'language' => ['nullable', 'string', 'max:40'], 'position' => ['required', 'integer', 'between:1,1000000',
                Rule::unique('content_blocks', 'position')->where('problem_id', $problem instanceof Problem ? $problem->id : 0)->ignore($block instanceof ContentBlock ? $block->id : null)]];
    }
}
