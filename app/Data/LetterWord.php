<?php

namespace App\Data;

readonly class LetterWord
{
    /**
     * @param  string  $image  Path relative to the public directory.
     * @param  string  $audio  Path relative to the public directory.
     */
    public function __construct(
        public string $text,
        public string $image,
        public string $audio,
    ) {
        //
    }
}
