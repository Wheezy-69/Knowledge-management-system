<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProblemRequest;
use App\Http\Requests\ProblemSearchRequest;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use App\Services\ProblemSearch;
use App\Services\ProblemTags;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProblemController extends Controller
{
    public function index(ProblemSearchRequest $request, ProblemSearch $search): Response
    {
        $filters = $search->filters($request->validated());
        $filters['search'] = trim($filters['search'] ?? '');

        return Inertia::render('problems/Index', [
            'filters' => $filters, 'filterOptions' => $search->options(),
            'problems' => $search->results(Problem::query(), $filters),
        ]);
    }

    public function store(ProblemRequest $request, Vault $vault, Section $section): RedirectResponse
    {
        $problem = DB::transaction(function () use ($section, $request): Problem {
            $problem = $section->problems()->create($request->validated());
            app(ProblemTags::class)->sync($problem, $request->validated('tags') ?? []);

            return $problem;
        });

        return to_route('vaults.sections.problems.show', [$vault, $section, $problem])->with('success', 'Problem created.');
    }

    public function show(Vault $vault, Section $section, Problem $problem): Response
    {
        return Inertia::render('problems/Show', [
            'vault' => $vault,
            'section' => $section,
            'problem' => $problem->load(['attempts', 'blocks']),
            'related' => Problem::query()->whereIn('id', DB::table('problem_links')
                ->where('problem_id', $problem->id)->orWhere('related_problem_id', $problem->id)
                ->get()->flatMap(fn ($link) => [$link->problem_id, $link->related_problem_id]))
                ->whereKeyNot($problem->id)->with(['section:id,vault_id,name', 'section.vault:id,name'])->get(['id', 'section_id', 'title']),
            'linkCandidates' => Problem::query()->whereKeyNot($problem->id)->latest()->limit(500)->get(['id', 'title']),
        ]);
    }

    public function update(ProblemRequest $request, Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        DB::transaction(function () use ($problem, $request): void {
            $problem->update($request->validated());
            app(ProblemTags::class)->sync($problem, $request->validated('tags') ?? []);
        });

        return back()->with('success', 'Problem updated.');
    }

    public function destroy(Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        $problem->delete();

        return to_route('vaults.sections.show', [$vault, $section])->with('success', 'Problem and its attempts deleted.');
    }
}
