<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentBlockRequest;
use App\Models\ContentBlock;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContentBlockController extends Controller
{
    public function store(ContentBlockRequest $request, Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        $problem->blocks()->create($request->validated());

        return back()->with('success', 'Block added.');
    }

    public function update(ContentBlockRequest $request, Vault $vault, Section $section, Problem $problem, ContentBlock $block): RedirectResponse
    {
        $block->update($request->validated());

        return back()->with('success', 'Block updated.');
    }

    public function destroy(Vault $vault, Section $section, Problem $problem, ContentBlock $block): RedirectResponse
    {
        $block->delete();

        return back()->with('success', 'Block deleted. Legacy fields were preserved.');
    }

    public function convert(Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        DB::transaction(function () use ($problem): void {
            $position = (int) $problem->blocks()->max('position');
            foreach (['description', 'corrected_solution', 'explanation'] as $source) {
                $content = $problem->getAttribute($source);
                if (! is_string($content) || trim($content) === '' || $problem->blocks()->where('source', $source)->exists()) {
                    continue;
                }
                if ($position >= 1000000) {
                    throw ValidationException::withMessages(['position' => 'No order numbers remain. Reorder blocks before converting.']);
                }
                $block = new ContentBlock(['type' => $source === 'corrected_solution' ? 'code' : 'text',
                    'content' => $content, 'language' => $source === 'corrected_solution' ? $problem->programming_language : null, 'position' => ++$position]);
                $block->source = $source;
                $problem->blocks()->save($block);
            }
        });

        return back()->with('success', 'Legacy content copied into blocks. Original fields remain unchanged.');
    }
}
