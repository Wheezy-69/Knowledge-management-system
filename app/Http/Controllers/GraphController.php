<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProblemSearchRequest;
use App\Models\Problem;
use App\Services\ProblemSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GraphController extends Controller
{
    public function index(ProblemSearchRequest $request, ProblemSearch $search): Response
    {
        $filters = $search->filters($request->validated());
        $query = Problem::query();
        if (! empty($filters['search'])) {
            $query->whereLike('title', '%'.$filters['search'].'%');
        }
        if (! empty($filters['vault_id'])) {
            $query->whereHas('section', fn (Builder $q) => $q->where('vault_id', $filters['vault_id']));
        }
        if (! empty($filters['section_id'])) {
            $query->where('section_id', $filters['section_id']);
        }
        if (! empty($filters['language'])) {
            $query->where('programming_language', $filters['language']);
        }
        foreach ($filters['tag_ids'] ?? [] as $id) {
            $query->whereHas('tagRecords', fn (Builder $q) => $q->where('tags.id', $id));
        }
        $total = $query->count();
        $problems = $query->with(['tagRecords', 'section:id,vault_id'])->orderBy('id')->limit(30)->get(['id', 'section_id', 'title']);
        $tags = $problems->flatMap(fn (Problem $p) => $p->tagRecords)->unique('id')->sortBy('name');
        $shownTags = $tags->take(50);
        $nodes = [];
        $edges = [];
        foreach ($problems as $problem) {
            $nodes[] = ['id' => 'problem-'.$problem->id, 'label' => $problem->title, 'type' => 'problem',
                'url' => route('vaults.sections.problems.show', [$problem->section->vault_id, $problem->section_id, $problem->id])];
            foreach ($problem->tagRecords as $tag) {
                if ($shownTags->contains('id', $tag->id)) {
                    $edges[] = ['from' => 'problem-'.$problem->id, 'to' => 'tag-'.$tag->id, 'type' => 'tag'];
                }
            }
        }
        foreach ($shownTags as $tag) {
            $nodes[] = ['id' => 'tag-'.$tag->id, 'label' => $tag->name, 'type' => 'tag', 'url' => route('problems.index', ['tag_ids' => [$tag->id]])];
        }
        foreach (DB::table('problem_links')->whereIn('problem_id', $problems->modelKeys())->whereIn('related_problem_id', $problems->modelKeys())->get() as $link) {
            $edges[] = ['from' => 'problem-'.$link->problem_id, 'to' => 'problem-'.$link->related_problem_id, 'type' => 'related'];
        }

        return Inertia::render('Graph', ['nodes' => $nodes, 'edges' => $edges, 'filters' => $filters, 'filterOptions' => $search->options(), 'limited' => $total > 30 || $tags->count() > 50]);
    }
}
