<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** @property int $id @property string $name @property string $key */
#[Fillable(['name', 'key'])]
class Tag extends Model
{
    /** @return BelongsToMany<Problem, $this> */
    public function problems(): BelongsToMany
    {
        return $this->belongsToMany(Problem::class);
    }

    public static function keyFor(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name) ?? $name));
    }
}
