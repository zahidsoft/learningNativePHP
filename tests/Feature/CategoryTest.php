<?php

use App\Actions\Learning\RecordProgress;
use App\Enums\LearningCategory;
use App\Enums\ProgressKind;
use App\NativeComponents\Category;
use Native\Mobile\Testing\Native;

test('it shows every letter of the category', function () {
    Native::visit('/learn/vowels')
        ->assertScreen(Category::class)
        ->assertNavTitle('Bangla Vowels')
        ->assertSee('11 cards to learn')
        ->assertSee('অ')
        ->assertSee('ঔ')
        ->assertDontSee('Alif');
});

test('it opens the tapped letter', function () {
    Native::visit('/learn/vowels')
        ->tap('ই')
        ->assertNavigatedTo('/learn/vowels/2');
});

test('it starts from the first letter', function () {
    Native::visit('/learn/arabic')
        ->tap('Start from the first')
        ->assertNavigatedTo('/learn/arabic/0');
});

test('it opens the quiz', function () {
    Native::visit('/learn/numbers')
        ->tap('Quiz')
        ->assertNavigatedTo('/quiz');
});

test('it falls back to english letters for an unknown category', function () {
    Native::visit('/learn/klingon')
        ->assertNavTitle('English Letters')
        ->assertSee('26 cards to learn');
});

test('no node changes its element type as letters are heard and mastered', function () {
    $screen = Native::visit('/learn/english');

    expectStableNodeTypes($screen, [
        fn () => $screen->tap('Start from the first')->follow()->goBack(),
        function () use ($screen) {
            app(RecordProgress::class)->answered(ProgressKind::Letter, LearningCategory::English->value, 'A', isCorrect: true);

            return $screen->tap('Start from the first')->follow()->goBack();
        },
    ]);
});
