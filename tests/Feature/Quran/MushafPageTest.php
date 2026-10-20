<?php

use App\Models\Khatam;
use App\NativeComponents\Quran\MushafPage;
use App\Services\Quran\QuranPreferences;
use Native\Mobile\Testing\Native;
use Tests\Fixtures\AlQuranFake;

test('it shows a page with its surah heading and ayah markers', function () {
    AlQuranFake::serve();

    Native::visit('/quran/page/1')
        ->assertScreen(MushafPage::class)
        ->assertNavTitle('পৃষ্ঠা ১')
        ->assertSee('سُورَةُ ٱلْفَاتِحَةِ')
        ->assertSee('﴿٧﴾');
});

test('it turns to the next page and counts the one read in the khatam', function () {
    AlQuranFake::serve();

    Native::visit('/quran/page/1')
        ->tap('পরের পৃষ্ঠা')
        ->assertSet('page', 2)
        ->assertSet('countedPages', [1]);

    expect(Khatam::current()->ayahs()->count())->toBe(7)
        ->and(app(QuranPreferences::class)->lastPage())->toBe(2);
});

test('it counts a page only once', function () {
    AlQuranFake::serve();

    Native::visit('/quran/page/1')
        ->call('countPageRead')
        ->call('countPageRead')
        ->assertSet('countedPages', [1]);

    expect(Khatam::current()->ayahs()->count())->toBe(7);
});

test('it goes back a page but not before the first', function () {
    AlQuranFake::serve();

    Native::visit('/quran/page/2')
        ->tap('আগের পৃষ্ঠা')
        ->assertSet('page', 1)
        ->tap('আগের পৃষ্ঠা')
        ->assertSet('page', 1);
});

test('it jumps to the first page of a juz', function () {
    AlQuranFake::serve();

    Native::visit('/quran/page/1')
        ->call('toggleJuzPicker')
        ->assertSee('কোন পারায় যাবেন?')
        ->tap('৩০')
        ->assertSet('page', 582)
        ->assertSet('isPickingJuz', false);
});

test('it opens the last page read when no page is given', function () {
    AlQuranFake::serve();
    app(QuranPreferences::class)->setLastPage(77);

    Native::test(MushafPage::class)->assertSet('page', 77);
});

test('it keeps the page number inside the muṣḥaf', function () {
    AlQuranFake::serve();

    Native::visit('/quran/page/900')->assertSet('page', 604);
});
