<?php

use App\Data\Letter;
use App\Enums\LearningCategory;

test('every category lists its letters once each, with a name', function (LearningCategory $category) {
    $letters = $category->letters();
    $characters = array_map(fn (Letter $letter): string => $letter->character, $letters);

    expect($letters)->not->toBeEmpty()
        ->and(array_unique($characters))->toBe($characters)
        ->and(array_filter($letters, fn (Letter $letter): bool => trim($letter->name) === ''))->toBe([]);
})->with(LearningCategory::cases());

test('it reads the letter, its word and its sounds from the content file', function () {
    expect(LearningCategory::English->letters()[2])->toEqual(new Letter('C', 'See', 'Cat', phonemes: ['k', 's']));
});

test('a letter without its own word takes the word of its category', function () {
    expect(LearningCategory::Vowels->letters()[0]->word)->toBe('স্বরবর্ণ')
        ->and(LearningCategory::Numbers->letters()[0]->word)->toBe('১');
});

test('every phonics sound in the content has a recording', function () {
    foreach (LearningCategory::allLetters() as ['letter' => $letter]) {
        foreach ($letter->phonemes as $symbol) {
            expect(public_path("audio/phoneme_audio/{$symbol}_isolation.mp3"))->toBeFile();
        }
    }
});
