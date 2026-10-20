<?php

use App\Enums\LearningCategory;
use App\NativeComponents\Letter;
use Native\Mobile\Testing\Native;

/**
 * Make the fake native bridge report that recorded clips start playing.
 */
function clipsPlay(): void
{
    Native::fakeBridge()->respondTo('Speech.PlayFile', ['playing' => true]);
}

test('it shows the letter with its name and example word', function () {
    Native::visit('/learn/english/1')
        ->assertScreen(Letter::class)
        ->assertNavTitle('English Letters')
        ->assertSee('Bee')
        ->assertSee('Ball')
        ->assertSet('index', 1);
});

test('it plays the recorded clip of the letter when it opens', function () {
    clipsPlay();

    Native::visit('/learn/english/0')
        ->assertNativeCalled('Speech.PlayFile', fn (array $params) => str_ends_with(
            str_replace('\\', '/', $params['path']),
            'public/audio/letters/englishLetter/ay.mp3',
        ))
        ->assertNativeNotCalled('Speech.Speak');
});

test('it plays the recorded clip of an arabic letter', function (int $index, string $clip) {
    clipsPlay();

    Native::visit("/learn/arabic/{$index}")
        ->assertNativeCalled('Speech.PlayFile', fn (array $params) => str_ends_with(
            str_replace('\\', '/', $params['path']),
            "public/audio/letters/arbiLetter/{$clip}",
        ))
        ->assertNativeNotCalled('Speech.Speak');
})->with([
    'alif' => [0, '01-alif.mp3'],
    'haa, numbered after waw in the recordings' => [25, '27-ha.mp3'],
    'hamza' => [27, '28-hamza.mp3'],
    'ya' => [28, '29-yaa.mp3'],
]);

test('every arabic letter has a recording', function () {
    foreach (LearningCategory::Arabic->letters() as $letter) {
        expect(public_path(LearningCategory::Arabic->letterClip($letter)))->toBeFile();
    }
});

test('it reads the letter with text to speech when it has no recording', function () {
    clipsPlay();

    Native::visit('/learn/numbers/0')
        ->assertNativeNotCalled('Speech.PlayFile')
        ->assertNativeCalled('Speech.Speak', fn (array $params) => $params['text'] === 'One' && $params['lang'] === 'en-US');
});

test('it reads the letter with text to speech when the recording cannot start', function () {
    Native::visit('/learn/english/0')
        ->assertNativeCalled('Speech.PlayFile')
        ->assertNativeCalled('Speech.Speak', fn (array $params) => $params['text'] === 'Ay');
});

test('it says the letter again when the card is tapped', function () {
    Native::visit('/learn/arabic/0')
        ->tap('Tap to hear')
        ->assertNativeCalledTimes('Speech.Speak', 2);
});

test('it moves to the next letter and says it', function () {
    Native::visit('/learn/arabic/0')
        ->tap('Next')
        ->assertSet('index', 1)
        ->assertSee('Ba')
        ->assertNativeCalled('Speech.Speak', fn (array $params) => $params['text'] === 'Ba');
});

test('it wraps from the last letter to the first', function () {
    Native::visit('/learn/vowels/10')
        ->tap('Next')
        ->assertSet('index', 0);
});

test('it wraps from the first letter to the last', function () {
    Native::visit('/learn/vowels/0')
        ->tap('Previous')
        ->assertSet('index', 10);
});

test('it jumps to a letter picked from the ribbon', function () {
    Native::visit('/learn/vowels/0')
        ->tap('ই')
        ->assertSet('index', 2);
});

test('it keeps an out of range position inside the category', function () {
    Native::visit('/learn/vowels/11')
        ->assertSet('index', 0);
});

test('it plays the recording of a picture word', function () {
    clipsPlay();

    Native::visit('/learn/english/0')
        ->tap('Ant')
        ->assertNativeCalled('Speech.PlayFile', fn (array $params) => str_ends_with(
            str_replace('\\', '/', $params['path']),
            'public/audio/words/english/ant.mp3',
        ));
});

test('it plays the phonics sound of the letter', function () {
    clipsPlay();

    Native::visit('/learn/english/0')
        ->tap('/æ/')
        ->assertNativeCalled('Speech.PlayFile', fn (array $params) => str_ends_with(
            str_replace('\\', '/', $params['path']),
            'public/audio/phoneme_audio/æ_isolation.mp3',
        ));
});

test('it shows phonics and picture words only for english letters', function () {
    Native::visit('/learn/english/0')
        ->assertSee('Letter sound')
        ->assertSee('Words with A');

    Native::visit('/learn/arabic/0')
        ->assertDontSee('Letter sound')
        ->assertDontSee('Words with');
});

test('it shows counting dots only for numbers', function () {
    Native::visit('/learn/numbers/4')->assertSee('Count the dots');

    Native::visit('/learn/english/4')->assertDontSee('Count the dots');
});
