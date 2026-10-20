<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One full reading of the Quran, from its start until every ayah is read.
 *
 * @property int $id
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Collection<int, KhatamAyah> $ayahs
 */
class Khatam extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the ayahs read during this khatam.
     *
     * @return HasMany<KhatamAyah, $this>
     */
    public function ayahs(): HasMany
    {
        return $this->hasMany(KhatamAyah::class);
    }

    /**
     * Get the khatam in progress, starting one when there is none.
     */
    public static function current(): self
    {
        return self::query()->whereNull('completed_at')->latest('id')->first()
            ?? self::query()->create();
    }

    /**
     * Get the latest khatam, finished or not, so a just-finished one can be
     * celebrated before the next begins.
     */
    public static function latestOrStart(): self
    {
        return self::query()->latest('id')->first() ?? self::query()->create();
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }
}
