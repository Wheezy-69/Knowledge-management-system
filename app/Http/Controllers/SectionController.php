<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProblemSearchRequest;
use App\Http\Requests\SectionRequest;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use App\Services\ProblemSearch;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    public function store(SectionRequest $request, Vault $vault): RedirectResponse
    {
        $section = $vault->sections()->create($request->validated());

        return to_route('vaults.sections.show', [$vault, $section])->with('success', 'Section created.');
    }

    public function show(ProblemSearchRequest $request, Vault $vault, Section $section, ProblemSearch $search): Response
    {
        $filters = $search->filters($request->validated());
        $filters['search'] = trim($filters['search'] ?? '');

        return Inertia::render('sections/Show', [
            'vault' => $vault, 'section' => $section, 'filters' => $filters,
            'filterOptions' => $search->options(),
            'problems' => $search->results(Problem::query()->where('section_id', $section->id), $filters),
        ]);
    }

    public function update(SectionRequest $request, Vault $vault, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        return back()->with('success', 'Section updated.');
    }

    public function destroy(Vault $vault, Section $section): RedirectResponse
    {
        $section->delete();

        return to_route('vaults.show', $vault)->with('success', 'Section and its contents deleted.');
    }
}
