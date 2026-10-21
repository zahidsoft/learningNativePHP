<?php

namespace App\NativeComponents;

use App\Actions\Learning\ListLetterWords;
use App\Actions\Learning\PlayLearningSound;
use App\Actions\Learning\RecordProgress;
use App\Data\Letter as LetterData;
use App\Enums\LearningCategory;
use App\Enums\ProgressKind;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Letter extends NativeComponent
{
    public string $category = LearningCategory::English->value;

    public int $index = 0;

    public function mount(): void
    {
        $category = LearningCategory::tryFrom((string) $this->param('category')) ?? LearningCategory::English;

        $this->category = $category->value;
        $this->index = $this->wrap((int) $this->param('index', 0));

        $this->hear();
    }

    public function render(): Element
    {
        $category = $this->currentCategory();
        $letter = $this->currentLetter();

        return $this->view('letter', [
            'category' => $category,
            'letter' => $letter,
            'letters' => $category->letters(),
            'index' => $this->index,
            'color' => LearningCategory::tileColor($this->index),
            'phonemes' => $this->phonemes(),
            'words' => app(ListLetterWords::class)->handle($category, $letter),
            'dotCount' => $category === LearningCategory::Numbers ? (int) $letter->character : 0,
        ]);
    }

    /**
     * Say the current letter aloud and count it as practised.
     */
    public function hear(): void
    {
        $category = $this->currentCategory();
        $letter = $this->currentLetter();

        app(PlayLearningSound::class)->letter($category, $letter);
        app(RecordProgress::class)->seen(ProgressKind::Letter, $category->value, $letter->character);
    }

    /**
     * Move to the next letter, wrapping from the last back to the first.
     */
    public function next(): void
    {
        $this->show($this->index + 1);
    }

    /**
     * Move to the previous letter, wrapping from the first back to the last.
     */
    public function previous(): void
    {
        $this->show($this->index - 1);
    }

    /**
     * Jump to the letter at the given position and say it aloud.
     */
    public function show(int $index): void
    {
        $this->index = $this->wrap($index);

        $this->hear();
    }

    /**
     * Play the phonics sound at the given position for the current letter.
     */
    public function playPhoneme(int $position): void
    {
        $symbol = $this->phonemes()[$position] ?? null;

        if ($symbol !== null) {
            app(PlayLearningSound::class)->clip("audio/phoneme_audio/{$symbol}_isolation.mp3");
        }
    }

    /**
     * Play the recording of the picture word at the given position.
     */
    public function playWord(int $position): void
    {
        $word = app(ListLetterWords::class)->handle($this->currentCategory(), $this->currentLetter())[$position] ?? null;

        if ($word !== null) {
            app(PlayLearningSound::class)->clip($word->audio);
        }
    }

    private function currentCategory(): LearningCategory
    {
        return LearningCategory::from($this->category);
    }

    private function currentLetter(): LetterData
    {
        return $this->currentCategory()->letters()[$this->index];
    }

    /**
     * The phonics sound(s) of the current letter, matching the
     * "<symbol>_isolation.mp3" clips in public/audio/phoneme_audio.
     *
     * @return list<string>
     */
    private function phonemes(): array
    {
        return $this->currentLetter()->phonemes;
    }

    /**
     * Keep a position inside the category, wrapping past either end.
     */
    private function wrap(int $index): int
    {
        $count = count($this->currentCategory()->letters());

        return (($index % $count) + $count) % $count;
    }
}
