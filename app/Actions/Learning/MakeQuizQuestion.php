<?php

namespace App\Actions\Learning;

use App\Enums\LearningCategory;

class MakeQuizQuestion
{
    /**
     * Pick a random letter from every category and three wrong names for it.
     *
     * @return array{character: string, font: string, answer: string, options: list<string>}
     */
    public function handle(): array
    {
        $entries = LearningCategory::allLetters();
        $picked = $entries[array_rand($entries)];
        $answer = $picked['letter'];

        $wrongNames = array_values(array_unique(array_filter(
            array_column(array_column($entries, 'letter'), 'name'),
            fn (string $name): bool => $name !== $answer->name,
        )));
        shuffle($wrongNames);

        $options = [$answer->name, ...array_slice($wrongNames, 0, 3)];
        shuffle($options);

        return [
            'character' => $answer->character,
            'font' => $picked['category']->font(),
            'answer' => $answer->name,
            'options' => $options,
        ];
    }
}
