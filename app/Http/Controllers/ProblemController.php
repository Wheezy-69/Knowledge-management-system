<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProblemRequest;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProblemController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:200']]);
        $search = trim($filters['search'] ?? '');

        return Inertia::render('problems/Index', [
            'filters' => ['search' => $search],
            'problems' => Problem::query()->with('section.vault')->withCount('attempts')
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('title', '%'.$search.'%')->orWhereLike('description', '%'.$search.'%')))
                ->latest('updated_at')->paginate(15)->withQueryString(),
        ]);
    }

    public function store(ProblemRequest $request, Vault $vault, Section $section): RedirectResponse
    {
        $problem = $section->problems()->create($request->validated());

        return to_route('vaults.sections.problems.show', [$vault, $section, $problem])->with('success', 'Problem created.');
    }

    public function show(Vault $vault, Section $section, Problem $problem): Response
    {
        return Inertia::render('problems/Show', [
            'vault' => $vault,
            'section' => $section,
            'problem' => $problem->load('attempts'),
        ]);
    }

    public function update(ProblemRequest $request, Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        $problem->update($request->validated());

        return back()->with('success', 'Problem updated.');
    }

    public function destroy(Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        $problem->delete();

        return to_route('vaults.sections.show', [$vault, $section])->with('success', 'Problem and its attempts deleted.');
    }
}
