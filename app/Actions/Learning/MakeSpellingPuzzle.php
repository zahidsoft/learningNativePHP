<?php

namespace App\Actions\Learning;

use App\Data\LetterWord;
use App\Enums\LearningCategory;
use App\Enums\SpellingLevel;
use RuntimeException;

class MakeSpellingPuzzle
{
    public function __construct(private ListWords $listWords)
    {
        //
    }

    /**
     * Pick a picture word for the level and mix its letters with a few decoys.
     *
     * @param  list<string>  $recentWords  Words just played, skipped while others remain.
     * @return array{word: string, image: string, audio: string, tiles: list<string>}
     */
    public function handle(SpellingLevel $level, array $recentWords = []): array
    {
        $words = $this->wordsFor($level);

        if ($words === []) {
            throw new RuntimeException("There are no spelling words for the {$level->value} level.");
        }

        $freshWords = array_values(array_filter(
            $words,
            fn (LetterWord $word): bool => ! in_array(mb_strtolower($word->text), $recentWords, true),
        ));

        $pool = $freshWords ?: $words;
        $word = $pool[array_rand($pool)];
        $letters = mb_str_split(mb_strtolower($word->text));

        $decoys = array_values(array_diff(range('a', 'z'), $letters));
        shuffle($decoys);

        $tiles = [...$letters, ...array_slice($decoys, 0, $level->decoyCount())];
        shuffle($tiles);

        return [
            'word' => mb_strtolower($word->text),
            'image' => $word->image,
            'audio' => $word->audio,
            'tiles' => $tiles,
        ];
    }

    /**
     * Get the plain single words whose length suits the level.
     *
     * @return list<LetterWord>
     */
    private function wordsFor(SpellingLevel $level): array
    {
        [$shortest, $longest] = $level->lengthRange();

        return array_values(array_filter(
            $this->listWords->handle(LearningCategory::English),
            function (LetterWord $word) use ($shortest, $longest): bool {
                $length = mb_strlen($word->text);

                return preg_match('/^[a-z]+$/i', $word->text) === 1
                    && $length >= $shortest
                    && $length <= $longest;
            },
        ));
    }
}
