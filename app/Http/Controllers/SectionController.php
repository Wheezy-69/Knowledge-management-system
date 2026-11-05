<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionRequest;
use App\Models\Section;
use App\Models\Vault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    public function store(SectionRequest $request, Vault $vault): RedirectResponse
    {
        $section = $vault->sections()->create($request->validated());

        return to_route('vaults.sections.show', [$vault, $section])->with('success', 'Section created.');
    }

    public function show(Request $request, Vault $vault, Section $section): Response
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:200']]);
        $search = trim($filters['search'] ?? '');

        return Inertia::render('sections/Show', [
            'vault' => $vault,
            'section' => $section,
            'filters' => ['search' => $search],
            'problems' => $section->problems()->withCount('attempts')
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->whereLike('title', '%'.$search.'%')->orWhereLike('description', '%'.$search.'%')))
                ->latest('updated_at')->paginate(15)->withQueryString(),
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
