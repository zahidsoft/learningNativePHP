<?php

use App\Actions\Learning\RecordProgress;
use App\Actions\Learning\SummarizeProgress;
use App\Enums\ProgressKind;
use App\Models\DailyActivity;
use App\Models\LearningProgress;
use App\NativeComponents\Home;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    $this->travelTo('2026-03-10 09:00');
});

test('the first visit to a letter earns a star and later visits do not', function () {
    $record = app(RecordProgress::class);

    $record->seen(ProgressKind::Letter, 'english', 'A');
    $record->seen(ProgressKind::Letter, 'english', 'A');

    expect(LearningProgress::query()->sole()->seen_count)->toBe(2)
        ->and(app(SummarizeProgress::class)->handle()->totalStars)->toBe(1);
});

test('a right answer masters the item and earns a star', function () {
    $progress = app(RecordProgress::class)->answered(ProgressKind::Letter, 'english', 'B', isCorrect: true);

    expect($progress->isMastered())->toBeTrue()
        ->and(app(SummarizeProgress::class)->handle()->totalStars)->toBe(1);
});

test('a wrong answer neither masters the item nor earns a star', function () {
    $progress = app(RecordProgress::class)->answered(ProgressKind::Letter, 'english', 'B', isCorrect: false);

    expect($progress->isMastered())->toBeFalse()
        ->and($progress->wrong_count)->toBe(1)
        ->and(app(SummarizeProgress::class)->handle()->totalStars)->toBe(0);
});

test('it counts learned letters per category', function () {
    $record = app(RecordProgress::class);
    $record->seen(ProgressKind::Letter, 'english', 'A');
    $record->seen(ProgressKind::Letter, 'english', 'B');
    $record->seen(ProgressKind::Letter, 'vowels', 'অ');
    $record->answered(ProgressKind::Word, 'english', 'cat', isCorrect: true);

    $summary = app(SummarizeProgress::class)->handle();

    expect($summary->learnedIn('english'))->toBe(2)
        ->and($summary->learnedIn('vowels'))->toBe(1)
        ->and($summary->learnedIn('arabic'))->toBe(0);
});

test('it counts the days in a row the app was used', function (array $activeDays, int $streak) {
    foreach ($activeDays as $date) {
        DailyActivity::query()->create(['date' => $date]);
    }

    expect(app(SummarizeProgress::class)->handle()->streakDays)->toBe($streak);
})->with([
    'no activity' => [[], 0],
    'today only' => [['2026-03-10'], 1],
    'three days up to today' => [['2026-03-08', '2026-03-09', '2026-03-10'], 3],
    'up to yesterday, today not played yet' => [['2026-03-08', '2026-03-09'], 2],
    'broken by a missed day' => [['2026-03-07', '2026-03-09', '2026-03-10'], 2],
    'last played two days ago' => [['2026-03-07', '2026-03-08'], 0],
]);

test('opening a letter marks it learned in its category', function () {
    Native::visit('/learn/vowels/2');

    Native::visit('/learn/vowels')->assertSee('1 learned');
});

test('a right quiz answer masters the letter', function () {
    Native::visit('/quiz')
        ->set('question', [
            'category' => 'english',
            'character' => 'B',
            'font' => 'glyph',
            'answer' => 'Bee',
            'options' => ['Ay', 'Bee', 'See', 'Dee'],
        ])
        ->tap('Bee');

    expect(LearningProgress::query()->for(ProgressKind::Letter, 'english')->where('key', 'B')->sole()->isMastered())->toBeTrue();
});

test('the home screen shows the stars and streak', function () {
    app(RecordProgress::class)->seen(ProgressKind::Letter, 'english', 'A');
    app(RecordProgress::class)->seen(ProgressKind::Letter, 'english', 'B');

    Native::test(Home::class)
        ->assertSee('2 stars')
        ->assertSee('1 day streak')
        ->assertSee('2/26 cards');
});
