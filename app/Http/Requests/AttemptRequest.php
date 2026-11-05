<?php

namespace App\Http\Requests;

use App\Models\Attempt;
use App\Models\Problem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Problem $problem */
        $problem = $this->route('problem');
        /** @var Attempt|null $attempt */
        $attempt = $this->route('attempt');

        return [
            'code' => ['required', 'string', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'position' => ['required', 'integer', 'min:1', 'max:1000000',
                Rule::unique('attempts', 'position')->where('problem_id', $problem->id)->ignore($attempt?->id)],
        ];
    }
}
