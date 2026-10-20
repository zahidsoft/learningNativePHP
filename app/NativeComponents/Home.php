<?php

namespace App\NativeComponents;

use App\Actions\Learning\SummarizeProgress;
use App\Enums\LearningCategory;
use App\Services\Quran\QuranPreferences;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Home extends NativeComponent
{
    public function render(): Element
    {
        return $this->view('home', [
            'greeting' => $this->greeting(),
            'progress' => app(SummarizeProgress::class)->handle(),
            'categoryRows' => array_chunk(LearningCategory::cases(), 2),
            'lastRead' => app(QuranPreferences::class)->lastRead(),
        ]);
    }

    /**
     * Greet the learner according to the time of day.
     */
    private function greeting(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour < 12 => 'Good morning!',
            $hour < 17 => 'Good afternoon!',
            default => 'Good evening!',
        };
    }
}
