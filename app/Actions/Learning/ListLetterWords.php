<?php

namespace App\Actions\Learning;

use App\Data\Letter;
use App\Data\LetterWord;
use App\Enums\LearningCategory;

class ListLetterWords
{
    /**
     * List the picture words that start with the given letter.
     *
     * A word is every image in public/audio/words/<category>/ that has a
     * recording with the same name beside it, so dropping in a new
     * image + sound pair adds a word without a code change.
     *
     * @return list<LetterWord>
     */
    public function handle(LearningCategory $category, Letter $letter): array
    {
        if ($category !== LearningCategory::English) {
            return [];
        }

        $directory = "audio/words/{$category->value}";
        $prefix = mb_strtolower($letter->character);
        $images = glob(public_path("{$directory}/{$prefix}*.png")) ?: [];
        sort($images);

        $words = [];

        foreach ($images as $image) {
            $name = pathinfo($image, PATHINFO_FILENAME);

            if (! is_file(public_path("{$directory}/{$name}.mp3"))) {
                continue;
            }

            $words[] = new LetterWord(ucfirst($name), "{$directory}/{$name}.png", "{$directory}/{$name}.mp3");
        }

        return $words;
    }
}
