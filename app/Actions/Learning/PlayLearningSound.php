<?php

namespace App\Actions\Learning;

use App\Data\Letter;
use App\Enums\LearningCategory;
use Illuminate\Support\Str;
use Nativephp\Speech\Facades\Speech;

class PlayLearningSound
{
    /**
     * Say a letter aloud: its recorded voice clip when one has been added to
     * public/audio/letters/<category>/, otherwise the device's text-to-speech.
     */
    public function letter(LearningCategory $category, Letter $letter): void
    {
        $clip = "audio/letters/{$category->value}/".Str::slug($letter->name).'.mp3';

        if (! $this->clip($clip)) {
            Speech::speak($letter->name, $category->language(), 0.85);
        }
    }

    /**
     * Say a word aloud: its recording, otherwise the device's text-to-speech.
     */
    public function word(string $text, string $publicPath): void
    {
        if (! $this->clip($publicPath)) {
            Speech::speak($text, 'en-US', 0.85);
        }
    }

    /**
     * Say the quiz feedback line for a right or wrong answer.
     */
    public function quizFeedback(bool $isCorrect): void
    {
        $clip = 'audio/sfx/'.($isCorrect ? 'correct' : 'wrong').'.mp3';

        if (! $this->clip($clip)) {
            Speech::speak($isCorrect ? 'Correct' : 'Try again', 'en-US', 0.85);
        }
    }

    /**
     * Play a clip given its path relative to the public directory.
     *
     * Returns false when the clip is missing or could not be started.
     */
    public function clip(string $publicPath): bool
    {
        $absolutePath = public_path($publicPath);

        return is_file($absolutePath) && Speech::playFile($absolutePath);
    }
}
