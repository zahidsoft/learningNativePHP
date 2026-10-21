<?php

namespace App\Data;

readonly class Letter
{
    /**
     * @param  string|null  $clip  The file name of its recording, when it differs from the slugged name.
     * @param  list<string>  $phonemes  The phonics sound(s) the letter makes, matching the clips in public/audio/phoneme_audio.
     */
    public function __construct(
        public string $character,
        public string $name,
        public string $word = '',
        public ?string $clip = null,
        public array $phonemes = [],
    ) {
        //
    }
}
