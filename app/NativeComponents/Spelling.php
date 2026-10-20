<?php

namespace App\NativeComponents;

use App\Actions\Learning\MakeSpellingPuzzle;
use App\Actions\Learning\PlayLearningSound;
use App\Actions\Learning\RecordProgress;
use App\Data\Letter as LetterData;
use App\Enums\LearningCategory;
use App\Enums\ProgressKind;
use App\Enums\SpellingLevel;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Device;

class Spelling extends NativeComponent
{
    /**
     * Wrong taps on one letter before its tile is pointed out.
     */
    public const MISTAKES_BEFORE_HINT = 2;

    /**
     * How many recent words are kept out of the next puzzles.
     */
    private const RECENT_WORD_LIMIT = 10;

    public string $level = SpellingLevel::Easy->value;

    /**
     * @var array{word: string, image: string, audio: string, tiles: list<string>}
     */
    public array $puzzle = ['word' => '', 'image' => '', 'audio' => '', 'tiles' => []];

    /**
     * Positions of the tiles already placed, in spelling order.
     *
     * @var list<int>
     */
    public array $placedTiles = [];

    public int $mistakes = 0;

    public ?int $hintTile = null;

    public int $wordsSpelled = 0;

    /**
     * @var list<string>
     */
    public array $recentWords = [];

    public function mount(): void
    {
        $this->level = (SpellingLevel::tryFrom((string) $this->param('level')) ?? SpellingLevel::Easy)->value;

        $this->nextWord();
    }

    public function render(): Element
    {
        $level = SpellingLevel::from($this->level);
        $letters = $this->letters();

        return $this->view('spelling', [
            'levels' => SpellingLevel::cases(),
            'level' => $level,
            'puzzle' => $this->puzzle,
            'letters' => $letters,
            'placedCount' => count($this->placedTiles),
            'placedTiles' => $this->placedTiles,
            'hintTile' => $this->hintTile,
            'isComplete' => $this->isComplete(),
            'isPerfect' => $this->mistakes === 0,
            'wordsSpelled' => $this->wordsSpelled,
            'tileRows' => array_chunk($this->puzzle['tiles'], 4, preserve_keys: true),
        ]);
    }

    /**
     * Say the word to spell.
     */
    public function hearWord(): void
    {
        app(PlayLearningSound::class)->word($this->puzzle['word'], $this->puzzle['audio']);
    }

    /**
     * Place the tile at the given position into the next empty slot, if it
     * holds the letter that slot needs.
     */
    public function pickTile(int $position): void
    {
        $tile = $this->puzzle['tiles'][$position] ?? null;

        if ($tile === null || $this->isComplete() || in_array($position, $this->placedTiles, true)) {
            return;
        }

        if ($tile !== $this->letters()[count($this->placedTiles)]) {
            $this->missTile();

            return;
        }

        $this->placedTiles[] = $position;
        $this->hintTile = null;

        if ($this->isComplete()) {
            $this->finishWord();

            return;
        }

        $this->sayLetter($tile);
    }

    /**
     * Start a new word at the current level.
     */
    public function nextWord(): void
    {
        $this->puzzle = app(MakeSpellingPuzzle::class)->handle(SpellingLevel::from($this->level), $this->recentWords);
        $this->recentWords = array_slice([$this->puzzle['word'], ...$this->recentWords], 0, self::RECENT_WORD_LIMIT);
        $this->placedTiles = [];
        $this->mistakes = 0;
        $this->hintTile = null;

        $this->hearWord();
    }

    /**
     * Switch to another level and start a word from it.
     */
    public function chooseLevel(string $level): void
    {
        $chosen = SpellingLevel::tryFrom($level);

        if ($chosen === null || $chosen->value === $this->level) {
            return;
        }

        $this->level = $chosen->value;
        $this->recentWords = [];

        $this->nextWord();
    }

    /**
     * React to a tile that does not fit the next slot: buzz, encourage, and
     * point at the right tile once the child has tried a few times.
     */
    private function missTile(): void
    {
        $this->mistakes++;

        Device::vibrate();
        app(PlayLearningSound::class)->quizFeedback(false);

        if ($this->mistakes >= self::MISTAKES_BEFORE_HINT) {
            $this->hintTile = $this->nextTilePosition();
        }
    }

    private function finishWord(): void
    {
        $this->wordsSpelled++;

        app(RecordProgress::class)->answered(
            ProgressKind::Word,
            LearningCategory::English->value,
            $this->puzzle['word'],
            isCorrect: $this->mistakes === 0,
        );

        $this->hearWord();
    }

    private function sayLetter(string $character): void
    {
        $letter = collect(LearningCategory::English->letters())
            ->first(fn (LetterData $letter): bool => $letter->character === mb_strtoupper($character));

        if ($letter !== null) {
            app(PlayLearningSound::class)->letter(LearningCategory::English, $letter);
        }
    }

    /**
     * Find an unplaced tile holding the letter the next slot needs.
     */
    private function nextTilePosition(): ?int
    {
        $needed = $this->letters()[count($this->placedTiles)] ?? null;

        foreach ($this->puzzle['tiles'] as $position => $tile) {
            if ($tile === $needed && ! in_array($position, $this->placedTiles, true)) {
                return $position;
            }
        }

        return null;
    }

    private function isComplete(): bool
    {
        return $this->puzzle['word'] !== '' && count($this->placedTiles) === count($this->letters());
    }

    /**
     * @return list<string>
     */
    private function letters(): array
    {
        return mb_str_split($this->puzzle['word']);
    }
}
