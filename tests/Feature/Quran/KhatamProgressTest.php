<?php

use App\Actions\Quran\RecordAyahsRead;
use App\Actions\Quran\SummarizeKhatam;
use App\Models\Khatam;
use App\Services\Quran\Mushaf;
use Native\Mobile\Testing\Native;
use Tests\Fixtures\AlQuranFake;

test('it sizes each juz from where the next one starts', function () {
    AlQuranFake::serve();

    $juzs = app(SummarizeKhatam::class)->handle()['juzs'];

    expect($juzs)->toHaveCount(2)
        ->and($juzs[0])->toMatchArray(['juz' => 1, 'total' => 7, 'startPage' => 1])
        ->and($juzs[1])->toMatchArray(['juz' => 2, 'total' => 6229, 'startPage' => 22]);
});

test('it shows how much of each juz is read', function () {
    AlQuranFake::serve();
    app(RecordAyahsRead::class)->handle([['number' => 1, 'juz' => 1], ['number' => 2, 'juz' => 1]]);

    Native::visit('/quran/khatam')
        ->assertSee('২ / ৬২৩৬ আয়াত পড়া হয়েছে')
        ->assertSee('২/৭');
});

test('it counts an ayah read twice only once', function () {
    $ayah = [['number' => 5, 'juz' => 1]];

    app(RecordAyahsRead::class)->handle($ayah);
    app(RecordAyahsRead::class)->handle($ayah);

    expect(Khatam::current()->ayahs()->count())->toBe(1);
});

test('it completes the khatam once every ayah is read and starts a new one after', function () {
    AlQuranFake::serve();

    $finished = app(RecordAyahsRead::class)->handle(array_map(
        fn (int $number): array => ['number' => $number, 'juz' => $number <= 7 ? 1 : 2],
        range(1, Mushaf::AYAH_COUNT),
    ));

    expect($finished->fresh()->isComplete())->toBeTrue();

    Native::visit('/quran/khatam')
        ->assertSee('আলহামদুলিল্লাহ!')
        ->tap('নতুন খতম শুরু করুন')
        ->assertSee('০%');

    expect(Khatam::query()->count())->toBe(2);
});

test('it opens the muṣḥaf at the start of a juz', function () {
    AlQuranFake::serve();

    Native::visit('/quran/khatam')
        ->tap('পারা ২')
        ->assertNavigatedTo('/quran/page/22');
});
