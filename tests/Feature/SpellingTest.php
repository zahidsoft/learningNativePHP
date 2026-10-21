<?php

use App\Actions\Learning\MakeSpellingPuzzle;
use App\Enums\ProgressKind;
use App\Enums\SpellingLevel;
use App\Models\LearningProgress;
use App\NativeComponents\Spelling;
use Native\Mobile\Testing\Native;
use Native\Mobile\Testing\TestableComponent;

/**
 * Open the spelling game on a known puzzle: "cat", with "x" and "m" as decoys.
 * Tile positions: t=0, x=1, a=2, c=3, m=4.
 */
function spellingCat(): TestableComponent
{
    return Native::visit('/spell')->set('puzzle', [
        'word' => 'cat',
        'image' => 'audio/words/english/cat.png',
        'audio' => 'audio/words/english/cat.mp3',
        'tiles' => ['t', 'x', 'a', 'c', 'm'],
    ]);
}

test('it starts on an easy word and says it', function () {
    Native::visit('/spell')
        ->assertScreen(Spelling::class)
        ->assertNavTitle('Spell It')
        ->assertSet('level', 'easy')
        ->assertSee('Tap the letters in order')
        ->assertNativeCalled('Speech.PlayFile');
});

test('it places a tile that fits the next slot', function () {
    spellingCat()
        ->call('pickTile', 3)
        ->assertSet('placedTiles', [3])
        ->assertSet('mistakes', 0);
});

test('it keeps a tile out of order off the word and counts the mistake', function () {
    spellingCat()
        ->call('pickTile', 0)
        ->assertSet('placedTiles', [])
        ->assertSet('mistakes', 1)
        ->assertNativeCalled('Speech.Speak', fn (array $params) => $params['text'] === 'Try again');
});

test('it points at the right tile after repeated mistakes', function () {
    spellingCat()
        ->call('pickTile', 1)
        ->assertSet('hintTile', null)
        ->call('pickTile', 4)
        ->assertSet('hintTile', 3)
        ->call('pickTile', 3)
        ->assertSet('hintTile', null);
});

test('it celebrates a perfectly spelled word and masters it', function () {
    spellingCat()
        ->call('pickTile', 3)
        ->call('pickTile', 2)
        ->call('pickTile', 0)
        ->assertSee('Perfect!')
        ->assertSet('wordsSpelled', 1);

    $progress = LearningProgress::query()->for(ProgressKind::Word, 'english')->where('key', 'cat')->sole();

    expect($progress->isMastered())->toBeTrue();
});

test('it does not master a word spelled with mistakes', function () {
    spellingCat()
        ->call('pickTile', 1)
        ->call('pickTile', 3)
        ->call('pickTile', 2)
        ->call('pickTile', 0)
        ->assertSee('You did it!');

    $progress = LearningProgress::query()->for(ProgressKind::Word, 'english')->where('key', 'cat')->sole();

    expect($progress->isMastered())->toBeFalse()
        ->and($progress->wrong_count)->toBe(1);
});

test('it moves on to a fresh word', function () {
    spellingCat()
        ->call('pickTile', 3)
        ->call('pickTile', 2)
        ->call('pickTile', 0)
        ->tap('Next word')
        ->assertSet('placedTiles', [])
        ->assertSet('mistakes', 0);
});

test('it switches to a harder level', function () {
    $screen = Native::visit('/spell')->tap('Medium')->assertSet('level', 'medium');

    expect(mb_strlen($screen->get('puzzle')['word']))->toBe(4);
});

test('a puzzle holds every letter of its word plus the decoys', function (SpellingLevel $level) {
    $puzzle = app(MakeSpellingPuzzle::class)->handle($level);
    [$shortest, $longest] = $level->lengthRange();
    $letters = mb_str_split($puzzle['word']);

    expect(count($letters))->toBeGreaterThanOrEqual($shortest)->toBeLessThanOrEqual($longest)
        ->and($puzzle['tiles'])->toHaveCount(count($letters) + $level->decoyCount());

    foreach (array_count_values($letters) as $letter => $count) {
        expect(array_count_values($puzzle['tiles'])[$letter])->toBe($count);
    }
})->with(SpellingLevel::cases());

test('a puzzle avoids the words just played', function () {
    $first = app(MakeSpellingPuzzle::class)->handle(SpellingLevel::Medium);
    $second = app(MakeSpellingPuzzle::class)->handle(SpellingLevel::Medium, [$first['word']]);

    expect($second['word'])->not->toBe($first['word']);
});

test('no node changes its element type while a word is spelled', function () {
    $screen = spellingCat();

    expectStableNodeTypes($screen, [
        fn () => $screen->call('pickTile', 1),
        fn () => $screen->call('pickTile', 4),
        fn () => $screen->call('pickTile', 3),
        fn () => $screen->call('pickTile', 2),
        fn () => $screen->call('pickTile', 0),
        fn () => $screen->call('nextWord'),
        fn () => $screen->call('chooseLevel', SpellingLevel::Hard->value),
    ]);
});
