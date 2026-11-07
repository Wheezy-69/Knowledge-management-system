<?php

namespace App\Services;

use App\Models\Attempt;
use App\Models\ContentBlock;
use App\Models\Problem;
use App\Models\Section;
use App\Models\Tag;
use App\Models\Vault;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProblemSearch
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function filters(array $input): array
    {
        return ['search' => trim($input['search'] ?? ''), 'vault_id' => empty($input['vault_id']) ? '' : (int) $input['vault_id'],
            'section_id' => empty($input['section_id']) ? '' : (int) $input['section_id'], 'language' => $input['language'] ?? '',
            'tag_ids' => array_map(intval(...), $input['tag_ids'] ?? [])];
    }

    /**
     * @param  Builder<Problem>  $query
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Problem>
     */
    public function results(Builder $query, array $filters): LengthAwarePaginator
    {
        $term = trim($filters['search'] ?? '');
        $query->select(['problems.id', 'section_id', 'title', 'description', 'programming_language', 'tags', 'problems.updated_at'])
            ->with(['section:id,vault_id,name', 'section.vault:id,name'])->withCount('attempts');
        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(fn (Builder $q) => $q->whereLike('title', $like)->orWhereLike('description', $like)
                ->orWhereLike('explanation', $like)->orWhereLike('corrected_solution', $like)
                ->orWhereHas('attempts', fn (Builder $a) => $a->whereLike('code', $like)->orWhereLike('notes', $like))
                ->orWhereHas('blocks', fn (Builder $b) => $b->whereLike('content', $like)));
            $query->addSelect(['corrected_solution', 'explanation'])
                ->addSelect(['matched_attempt' => Attempt::query()->select('code')->whereColumn('problem_id', 'problems.id')->whereLike('code', $like)->limit(1)])
                ->addSelect(['matched_note' => Attempt::query()->select('notes')->whereColumn('problem_id', 'problems.id')->whereLike('notes', $like)->limit(1)])
                ->addSelect(['matched_block' => ContentBlock::query()->select('content')->whereColumn('problem_id', 'problems.id')->whereLike('content', $like)->limit(1)]);
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
        $result = $query->latest('problems.updated_at')->orderByDesc('problems.id')->paginate(15)->withQueryString();
        foreach ($result->items() as $problem) {
            $problem->makeHidden(['corrected_solution', 'explanation', 'matched_attempt', 'matched_note', 'matched_block']);
            if ($term === '') {
                continue;
            }
            foreach (['title' => 'Title', 'description' => 'Description', 'explanation' => 'Explanation', 'corrected_solution' => 'Solution', 'matched_attempt' => 'Attempt', 'matched_note' => 'Attempt notes', 'matched_block' => 'Block'] as $field => $label) {
                $text = $problem->getAttribute($field);
                if (! is_string($text)) {
                    continue;
                }
                $offset = mb_stripos($text, $term);
                if ($offset === false && ! str_starts_with($field, 'matched_')) {
                    continue;
                }
                $start = max(0, ($offset === false ? 0 : $offset) - 60);
                $problem->setAttribute('preview', ['field' => $label, 'text' => ($start ? '…' : '').Str::substr($text, $start, 240).(mb_strlen($text) > $start + 240 ? '…' : '')]);
                break;
            }
        }

        return $result;
    }

    /** @return array<string, mixed> */
    public function options(): array
    {
        return ['vaults' => Vault::query()->orderBy('name')->get(['id', 'name']),
            'sections' => Section::query()->orderBy('name')->get(['id', 'vault_id', 'name']),
            'languages' => Problem::query()->distinct()->orderBy('programming_language')->pluck('programming_language'),
            'tags' => Tag::query()->orderBy('name')->get(['id', 'name'])];
    }
}
