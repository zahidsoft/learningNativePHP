<?php

use App\Actions\Learning\ListLetterWords;
use App\Data\Letter;
use App\Enums\LearningCategory;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->publicRoot = sys_get_temp_dir().'/learn-letter-words-'.uniqid();
    $words = "{$this->publicRoot}/audio/words/english";

    File::ensureDirectoryExists($words);

    // "apple" and "ant": complete pairs. "axe": image only. "bag": another letter.
    foreach (['apple.png', 'apple.mp3', 'ant.png', 'ant.mp3', 'axe.png', 'bag.png', 'bag.mp3'] as $file) {
        File::put("{$words}/{$file}", 'x');
    }

    $this->app->usePublicPath($this->publicRoot);
});

afterEach(function () {
    File::deleteDirectory($this->publicRoot);
});

test('it lists the words of a letter that have both a picture and a recording', function () {
    $words = app(ListLetterWords::class)->handle(LearningCategory::English, new Letter('A', 'Ay'));

    expect(array_column($words, 'text'))->toBe(['Ant', 'Apple'])
        ->and($words[0]->image)->toBe('audio/words/english/ant.png')
        ->and($words[0]->audio)->toBe('audio/words/english/ant.mp3');
});

test('it lists no words outside the english category', function () {
    $words = app(ListLetterWords::class)->handle(LearningCategory::Numbers, new Letter('a', 'One'));

    expect($words)->toBe([]);
});
