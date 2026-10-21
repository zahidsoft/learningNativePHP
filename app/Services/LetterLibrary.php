<?php

namespace App\Services;

use App\Data\Letter;
use App\Enums\LearningCategory;
use Illuminate\Container\Attributes\Singleton;
use JsonException;
use UnexpectedValueException;

/**
 * Reads the letters each category teaches from resources/content/letters,
 * so lessons can be edited or added without touching PHP.
 *
 * Each file is read once per process; screens ask for letters on every render.
 */
#[Singleton]
class LetterLibrary
{
    /**
     * @var array<string, list<Letter>>
     */
    private array $letters = [];

    /**
     * Get every letter in the category, in learning order.
     *
     * @return list<Letter>
     */
    public function for(LearningCategory $category): array
    {
        return $this->letters[$category->value] ??= $this->load($category);
    }

    /**
     * @return list<Letter>
     */
    private function load(LearningCategory $category): array
    {
        $path = resource_path("content/letters/{$category->value}.json");

        try {
            /** @var array{word?: string, letters: list<array{character: string, name: string, word?: string, clip?: string, phonemes?: list<string>}>} $content */
            $content = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnexpectedValueException("The letters in [{$path}] are not valid JSON: {$exception->getMessage()}", previous: $exception);
        }

        return array_map(fn (array $row): Letter => new Letter(
            character: $row['character'],
            name: $row['name'],
            word: $row['word'] ?? $content['word'] ?? '',
            clip: $row['clip'] ?? null,
            phonemes: $row['phonemes'] ?? [],
        ), $content['letters']);
    }
}
