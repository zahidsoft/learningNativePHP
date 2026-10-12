<?php

namespace App\Data;

readonly class Letter
{
    public function __construct(
        public string $character,
        public string $name,
        public string $word = '',
    ) {
        //
    }
}
