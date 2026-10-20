<?php

use App\Enums\LearningCategory;
use App\NativeComponents\Home;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    // January 1st makes the letter of the day the very first one: English "A".
    $this->travelTo('2026-01-01');
});

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

test('it links the letter of the day to that letter', function () {
    Native::test(Home::class)
        ->tap('Letter of the day')
        ->assertNavigatedTo('/learn/english/0');
});
