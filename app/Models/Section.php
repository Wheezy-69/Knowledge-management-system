<?php

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $vault_id
 * @property string $name
 * @property string|null $description
 * @property-read Vault $vault
 */
#[Fillable(['name', 'description'])]
class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    /** @return BelongsTo<Vault, $this> */
    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    /** @return HasMany<Problem, $this> */
    public function problems(): HasMany
    {
        return $this->hasMany(Problem::class);
    }
}
