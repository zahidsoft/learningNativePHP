<?php

namespace App\Actions\Learning;

use App\Data\LetterWord;
use App\Enums\LearningCategory;

class ListWords
{
    /**
     * Picture formats a word image may be saved in.
     *
     * @var list<string>
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * List the picture words of a category, optionally only those that start
     * with the given prefix.
     *
     * A word is every image in public/audio/words/<category>/ that has a
     * recording with the same name beside it, so dropping in a new
     * image + sound pair adds a word without a code change.
     *
     * @return list<LetterWord>
     */
    public function handle(LearningCategory $category, string $prefix = ''): array
    {
        $directory = "audio/words/{$category->value}";
        $files = glob(public_path("{$directory}/".mb_strtolower($prefix).'*')) ?: [];
        sort($files);

        $words = [];

        foreach ($files as $file) {
            if (! in_array(mb_strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $name = pathinfo($file, PATHINFO_FILENAME);

            if (! is_file(public_path("{$directory}/{$name}.mp3"))) {
                continue;
            }

            $words[] = new LetterWord(ucfirst($name), "{$directory}/".basename($file), "{$directory}/{$name}.mp3");
        }

        return $words;
    }
}
