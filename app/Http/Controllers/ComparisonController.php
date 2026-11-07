<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Section;
use App\Models\Vault;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComparisonController extends Controller
{
    public function show(Request $request, Vault $vault, Section $section, Problem $problem): Response
    {
        $data = $request->validate(['left' => ['nullable', 'string', 'max:30'], 'right' => ['nullable', 'string', 'max:30']]);
        $attempts = $problem->attempts()->get();
        $choices = [['id' => 'solution', 'label' => 'Corrected solution', 'code' => $problem->corrected_solution ?? '']];
        foreach ($attempts as $attempt) {
            $choices[] = ['id' => (string) $attempt->id, 'label' => 'Attempt '.$attempt->position, 'code' => $attempt->code];
        }
        $left = $data['left'] ?? ($choices[1]['id'] ?? 'solution');
        foreach ($problem->blocks()->where('type', 'code')->get() as $block) {
            $choices[] = ['id' => 'block-'.$block->id, 'label' => 'Code block '.$block->position, 'code' => $block->content];
        }
        $right = $data['right'] ?? 'solution';
        abort_unless(in_array($left, array_column($choices, 'id'), true) && in_array($right, array_column($choices, 'id'), true), 404);

        return Inertia::render('problems/Compare', compact('vault', 'section', 'problem', 'choices', 'left', 'right'));
    }
}
