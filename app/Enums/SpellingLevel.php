<?php

namespace App\Enums;

enum SpellingLevel: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    /**
     * Get the label shown on the level picker.
     */
    public function label(): string
    {
        return match ($this) {
            self::Easy => 'Easy',
            self::Medium => 'Medium',
            self::Hard => 'Hard',
        };
    }

    /**
     * Get the shortest and longest word the level asks for.
     *
     * @return array{int, int}
     */
    public function lengthRange(): array
    {
        return match ($this) {
            self::Easy => [2, 3],
            self::Medium => [4, 4],
            self::Hard => [5, 9],
        };
    }

    /**
     * Determine whether empty slots show a faint copy of their letter, so a
     * beginner matches shapes before spelling from memory.
     */
    public function showsGhostLetters(): bool
    {
        return $this === self::Easy;
    }

    /**
     * Get how many letters that are not in the word get mixed into the tiles.
     */
    public function decoyCount(): int
    {
        return match ($this) {
            self::Easy => 2,
            self::Medium, self::Hard => 3,
        };
    }
}
