<?php

namespace App\Actions\Learning;

use App\Data\ProgressSummary;
use App\Enums\ProgressKind;
use App\Models\DailyActivity;
use App\Models\LearningProgress;

class SummarizeProgress
{
    /**
     * Gather the learner's stars, daily streak and letters learned per category.
     */
    public function handle(): ProgressSummary
    {
        $learnedByCategory = LearningProgress::query()
            ->where('kind', ProgressKind::Letter)
            ->selectRaw('subject, count(*) as learned')
            ->groupBy('subject')
            ->pluck('learned', 'subject')
            ->map(fn (mixed $learned): int => (int) $learned)
            ->all();

        return new ProgressSummary(
            totalStars: (int) DailyActivity::query()->sum('stars'),
            streakDays: $this->streakDays(),
            learnedByCategory: $learnedByCategory,
        );
    }

    /**
     * Count the days in a row the app was used, ending today.
     *
     * A streak that reached yesterday still counts until today is over, so a
     * child opening the app in the morning does not see it reset to zero.
     */
    private function streakDays(): int
    {
        $activeDays = array_flip(DailyActivity::query()
            ->where('date', '>=', today()->subYear()->toDateString())
            ->pluck('date')
            ->all());

        $day = today()->toImmutable();

        if (! isset($activeDays[$day->toDateString()])) {
            $day = $day->subDay();
        }

        $streak = 0;

        while (isset($activeDays[$day->toDateString()])) {
            $streak++;
            $day = $day->subDay();
        }

        return $streak;
    }
}
