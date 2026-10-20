<?php

namespace App\Data;

readonly class ProgressSummary
{
    /**
     * @param  array<string, int>  $learnedByCategory  Letters seen at least once, keyed by category value.
     */
    public function __construct(
        public int $totalStars,
        public int $streakDays,
        public array $learnedByCategory,
    ) {
        //
    }

    /**
     * Get how many letters of the given category have been learned.
     */
    public function learnedIn(string $category): int
    {
        return $this->learnedByCategory[$category] ?? 0;
    }
}
