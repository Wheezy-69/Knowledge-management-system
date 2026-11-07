<?php

namespace App\Models;

use Database\Factories\ProblemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $section_id
 * @property string $title
 * @property string $programming_language
 * @property string|null $description
 * @property string|null $corrected_solution
 * @property string|null $explanation
 * @property list<string>|null $tags
 * @property-read Section $section
 */
#[Fillable(['title', 'description', 'programming_language', 'tags', 'corrected_solution', 'explanation'])]
class Problem extends Model
{
    /** @use HasFactory<ProblemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['tags' => 'array'];
    }

    /** @return BelongsTo<Section, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /** @return HasMany<Attempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class)->orderBy('position');
    }

    /** @return HasMany<ContentBlock, $this> */
    public function blocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)->orderBy('position');
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tagRecords(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'problem_tag');
    }
}
