<?php

namespace App\Actions\Learning;

use App\Data\Letter;
use App\Data\LetterWord;
use App\Enums\LearningCategory;

class ListLetterWords
{
    public function __construct(private ListWords $listWords)
    {
        //
    }

    /**
     * List the picture words that start with the given letter.
     *
     * @return list<LetterWord>
     */
    public function handle(LearningCategory $category, Letter $letter): array
    {
        if ($category !== LearningCategory::English) {
            return [];
        }

        return $this->listWords->handle($category, $letter->character);
    }
}
