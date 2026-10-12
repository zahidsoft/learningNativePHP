<?php

namespace App\NativeComponents;

use App\Actions\Learning\ListLetterWords;
use App\Actions\Learning\PlayLearningSound;
use App\Data\Letter as LetterData;
use App\Enums\LearningCategory;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Letter extends NativeComponent
{
    /**
     * The everyday phonics sound(s) each English letter makes, matching the
     * "<symbol>_isolation.mp3" clips in public/audio/phoneme_audio. C, G and X
     * list the two sounds a basic phonics curriculum teaches them with.
     *
     * @var array<string, list<string>>
     */
    private const PHONEMES = [
        'A' => ['æ'], 'B' => ['b'], 'C' => ['k', 's'], 'D' => ['d'], 'E' => ['e'], 'F' => ['f'],
        'G' => ['g', 'dʒ'], 'H' => ['h'], 'I' => ['ɪ'], 'J' => ['dʒ'], 'K' => ['k'], 'L' => ['l'],
        'M' => ['m'], 'N' => ['n'], 'O' => ['ɒ'], 'P' => ['p'], 'Q' => ['k'], 'R' => ['r'],
        'S' => ['s'], 'T' => ['t'], 'U' => ['ʌ'], 'V' => ['v'], 'W' => ['w'], 'X' => ['x', 'z'],
        'Y' => ['j'], 'Z' => ['z'],
    ];

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
     * Say the current letter aloud.
     */
    public function hear(): void
    {
        app(PlayLearningSound::class)->letter($this->currentCategory(), $this->currentLetter());
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
     * @return list<string>
     */
    private function phonemes(): array
    {
        if ($this->currentCategory() !== LearningCategory::English) {
            return [];
        }

        return self::PHONEMES[$this->currentLetter()->character] ?? [];
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
