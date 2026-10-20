<?php

use App\NativeComponents\Quran\SurahList;
use App\Services\Quran\QuranPreferences;
use Native\Mobile\Testing\Native;
use Tests\Fixtures\AlQuranFake;

test('it lists the surahs with their arabic names', function () {
    AlQuranFake::serve();

    Native::visit('/quran')
        ->assertScreen(SurahList::class)
        ->assertNavTitle('আল-কুরআন')
        ->assertSee('Al-Faatiha')
        ->assertSee('سُورَةُ الإِخۡلَاصِ')
        ->assertSee('মক্কী · ৭ আয়াত');
});

test('it filters the surahs by name or number', function (string $filter) {
    AlQuranFake::serve();

    Native::visit('/quran')
        ->set('filter', $filter)
        ->assertSee('Al-Ikhlaas')
        ->assertDontSee('Al-Faatiha');
})->with(['english name' => ['ikhlas'], 'meaning' => ['sincerity'], 'number' => ['112'], 'bangla number' => ['১১২']]);

test('it opens a surah', function () {
    AlQuranFake::serve();

    Native::visit('/quran')
        ->tap('Al-Ikhlaas')
        ->assertNavigatedTo('/quran/surah/112');
});

test('it offers to continue where the reader stopped', function () {
    AlQuranFake::serve();
    app(QuranPreferences::class)->setLastRead(1, 4, 'Al-Faatiha');

    Native::visit('/quran')
        ->tap('পড়া চালিয়ে যান')
        ->assertNavigatedTo('/quran/surah/1/4');
});

test('it opens the muṣḥaf at the last page read', function () {
    AlQuranFake::serve();
    app(QuranPreferences::class)->setLastPage(250);

    Native::visit('/quran')
        ->tap('মুসহাফ')
        ->assertNavigatedTo('/quran/page/250');
});

test('it explains when the list cannot be fetched', function () {
    AlQuranFake::offline();

    Native::visit('/quran')->assertSee('ইন্টারনেট সংযোগ পাওয়া যায়নি');
});
