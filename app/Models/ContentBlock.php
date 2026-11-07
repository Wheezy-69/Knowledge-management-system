<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property int $id @property int $problem_id @property string $type @property string $content @property string|null $language @property int $position @property string|null $source */
#[Fillable(['type', 'content', 'language', 'position'])]
class ContentBlock extends Model
{
    /** @return BelongsTo<Problem, $this> */
    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
    }
}
