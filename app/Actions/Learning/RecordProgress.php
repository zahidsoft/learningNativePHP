<?php

namespace App\Actions\Learning;

use App\Enums\ProgressKind;
use App\Models\DailyActivity;
use App\Models\LearningProgress;

class RecordProgress
{
    /**
     * Note that the learner looked at or listened to an item.
     *
     * The first visit to an item earns a star, and any visit keeps the
     * daily streak alive.
     */
    public function seen(ProgressKind $kind, string $subject, string $key): LearningProgress
    {
        $progress = $this->find($kind, $subject, $key);
        $isFirstVisit = ! $progress->exists;

        $progress->seen_count++;
        $progress->last_seen_at = now();
        $progress->save();

        $this->awardStars($isFirstVisit ? 1 : 0);

        return $progress;
    }

    /**
     * Note the learner's answer about an item in a quiz or game.
     *
     * A right answer masters the item and earns a star.
     */
    public function answered(ProgressKind $kind, string $subject, string $key, bool $isCorrect): LearningProgress
    {
        $progress = $this->find($kind, $subject, $key);

        if ($isCorrect) {
            $progress->correct_count++;
            $progress->mastered_at ??= now();
        } else {
            $progress->wrong_count++;
        }

        $progress->save();

        $this->awardStars($isCorrect ? 1 : 0);

        return $progress;
    }

    private function find(ProgressKind $kind, string $subject, string $key): LearningProgress
    {
        return LearningProgress::query()->firstOrNew([
            'kind' => $kind,
            'subject' => $subject,
            'key' => $key,
        ]);
    }

    /**
     * Add stars to today's tally, creating today's row so the streak counts it.
     */
    private function awardStars(int $stars): void
    {
        $today = DailyActivity::today();

        if ($stars > 0) {
            $today->increment('stars', $stars);
        }
    }
}
