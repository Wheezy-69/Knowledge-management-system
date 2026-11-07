<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RelatedProblemController extends Controller
{
    public function store(Request $request, Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        $data = $request->validate(['related_problem_id' => ['required', 'integer', 'exists:problems,id', 'different:problem_id']]);
        $other = (int) $data['related_problem_id'];
        if ($other === $problem->id) {
            throw ValidationException::withMessages(['related_problem_id' => 'A problem cannot link to itself.']);
        }
        $inserted = DB::table('problem_links')->insertOrIgnore(['problem_id' => min($problem->id, $other), 'related_problem_id' => max($problem->id, $other)]);
        if (! $inserted) {
            throw ValidationException::withMessages(['related_problem_id' => 'These problems are already linked.']);
        }

        return back()->with('success', 'Problems linked.');
    }

    public function destroy(Vault $vault, Section $section, Problem $problem, int $related): RedirectResponse
    {
        DB::table('problem_links')->where('problem_id', min($problem->id, $related))->where('related_problem_id', max($problem->id, $related))->delete();

        return back()->with('success', 'Relationship removed; both problems were preserved.');
    }
}
