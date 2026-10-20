<?php

use App\Enums\LearningCategory;
use App\NativeComponents\Home;
use App\Services\Quran\QuranPreferences;
use Native\Mobile\Testing\Native;

test('it lists every learning category with its card count', function () {
    $screen = Native::visit('/')->assertScreen(Home::class);

    foreach (LearningCategory::cases() as $category) {
        $screen->assertSee($category->title())
            ->assertSee('0/'.count($category->letters()).' cards');
    }
});

test('it greets the learner by the time of day', function (string $time, string $greeting) {
    $this->travelTo("2026-01-01 {$time}");

    Native::test(Home::class)->assertSee($greeting);
})->with([
    'morning' => ['08:00', 'Good morning!'],
    'noon' => ['12:00', 'Good afternoon!'],
    'evening' => ['17:00', 'Good evening!'],
]);

test('it opens a category when its card is tapped', function () {
    Native::test(Home::class)
        ->tap('Arabic Letters')
        ->assertNavigatedTo('/learn/arabic');
});

test('it opens the quiz from the quiz card', function () {
    Native::test(Home::class)
        ->tap('Quiz Time')
        ->assertNavigatedTo('/quiz');
});

test('it opens the quran from the quran card', function () {
    Native::test(Home::class)
        ->tap('কুরআন পড়ুন')
        ->assertNavigatedTo('/quran');
});

test('it continues the quran where the reader stopped', function () {
    app(QuranPreferences::class)->setLastRead(112, 3, 'Al-Ikhlaas');

    Native::test(Home::class)
        ->assertSee('পড়া চালিয়ে যান · আয়াত ৩')
        ->tap('Al-Ikhlaas')
        ->assertNavigatedTo('/quran/surah/112/3');
});
