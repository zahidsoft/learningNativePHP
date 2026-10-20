<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per day the learner used the app, holding the stars earned that day.
 *
 * @property int $id
 * @property string $date The day as Y-m-d; kept uncast so lookups match the stored value exactly.
 * @property int $stars
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['date'])]
class DailyActivity extends Model
{
    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'stars' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stars' => 'integer',
        ];
    }

    /**
     * Get today's row, creating it on the first activity of the day.
     */
    public static function today(): self
    {
        return self::query()->firstOrCreate(['date' => today()->toDateString()]);
    }
}
