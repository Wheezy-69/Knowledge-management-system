<?php

namespace App\Services;

use App\Models\Problem;
use App\Models\Tag;

class ProblemTags
{
    /** @param list<string> $names */
    public function sync(Problem $problem, array $names, bool $preserveLabels = false): void
    {
        $ids = [];
        $labels = [];
        foreach ($names as $name) {
            $key = Tag::keyFor($name);
            if ($key === '') {
                continue;
            }
            $tag = Tag::query()->firstOrCreate(['key' => $key], ['name' => trim($name)]);
            $ids[] = $tag->id;
            $labels[] = $tag->name;
        }
        $problem->tagRecords()->sync(array_unique($ids));
        if (! $preserveLabels) {
            $problem->update(['tags' => array_values(array_unique($labels))]);
        }
    }
}
