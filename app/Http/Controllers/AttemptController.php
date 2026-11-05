<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttemptRequest;
use App\Models\Attempt;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use Illuminate\Http\RedirectResponse;

class AttemptController extends Controller
{
    public function store(AttemptRequest $request, Vault $vault, Section $section, Problem $problem): RedirectResponse
    {
        $problem->attempts()->create($request->validated());

        return back()->with('success', 'Attempt saved.');
    }

    public function update(AttemptRequest $request, Vault $vault, Section $section, Problem $problem, Attempt $attempt): RedirectResponse
    {
        $attempt->update($request->validated());

        return back()->with('success', 'Attempt updated.');
    }

    public function destroy(Vault $vault, Section $section, Problem $problem, Attempt $attempt): RedirectResponse
    {
        $attempt->delete();

        return back()->with('success', 'Attempt deleted.');
    }
}
