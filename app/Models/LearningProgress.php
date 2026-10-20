<?php

namespace App\Models;

use App\Enums\ProgressKind;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * How far the learner has got with one thing to learn: a letter of a
 * category, a spelling word, and later sentences or stories.
 *
 * @property int $id
 * @property ProgressKind $kind
 * @property string $subject
 * @property string $key
 * @property int $seen_count
 * @property int $correct_count
 * @property int $wrong_count
 * @property CarbonImmutable|null $last_seen_at
 * @property CarbonImmutable|null $mastered_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['kind', 'subject', 'key'])]
class LearningProgress extends Model
{
    protected $table = 'learning_progress';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'seen_count' => 0,
        'correct_count' => 0,
        'wrong_count' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ProgressKind::class,
            'seen_count' => 'integer',
            'correct_count' => 'integer',
            'wrong_count' => 'integer',
            'last_seen_at' => 'datetime',
            'mastered_at' => 'datetime',
        ];
    }

    /**
     * Scope the query to one kind of item within one subject.
     *
     * @param  Builder<self>  $query
     */
    public function scopeFor(Builder $query, ProgressKind $kind, string $subject): void
    {
        $query->where('kind', $kind)->where('subject', $subject);
    }

    /**
     * Determine whether the learner has proven they know the item.
     */
    public function isMastered(): bool
    {
        return $this->mastered_at !== null;
    }
}
