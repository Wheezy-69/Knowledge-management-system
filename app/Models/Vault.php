<?php

namespace App\Models;

use Database\Factories\VaultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $cover_path
 */
#[Fillable(['name', 'description', 'cover_path'])]
#[Hidden(['cover_path'])]
class Vault extends Model
{
    /** @use HasFactory<VaultFactory> */
    use HasFactory;

    protected $appends = ['cover_url'];

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? route('vaults.cover', $this) : null;
    }

    /** @return HasMany<Section, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
