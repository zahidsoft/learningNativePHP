<?php

use App\Enums\Translation;
use App\Services\Quran\QuranPreferences;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Native\Mobile\AsyncTask;
use Native\Mobile\Testing\Native;
use Tests\Fixtures\AlQuranFake;

beforeEach(function () {
    AsyncTask::fake();
});

test('it finds the ayahs whose translation has the word', function () {
    AlQuranFake::serve();

    Native::visit('/quran/search')
        ->set('query', 'এক')
        ->call('search')
        ->assertSee('"এক" পাওয়া গেছে ১টি আয়াতে')
        ->assertSee('বলুন, তিনি আল্লাহ, এক')
        ->tap('বলুন, তিনি আল্লাহ, এক')
        ->assertNavigatedTo('/quran/surah/112/1');

    Http::assertSent(fn (Request $request) => str_contains(urldecode($request->url()), 'search/এক/all/bn.bengali'));
});

test('it searches the translation the reader chose', function () {
    AlQuranFake::serve();
    app(QuranPreferences::class)->setTranslation(Translation::ZohurulHoque);

    Native::visit('/quran/search')->set('query', 'এক')->call('search');

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/all/bn.hoque'));
});

test('it says when no ayah has the word', function () {
    AlQuranFake::serve(['api.alquran.cloud/v1/search/*' => Http::response(['code' => 404, 'status' => 'NOT FOUND', 'data' => 'Nothing matching your search was found..'], 404)]);

    Native::visit('/quran/search')
        ->set('query', 'xyz')
        ->call('search')
        ->assertSet('total', 0)
        ->assertSee('"xyz" শব্দটি কোনো আয়াতে পাওয়া যায়নি');
});

test('it does not search for nothing', function () {
    AlQuranFake::serve();

    Native::visit('/quran/search')->set('query', '   ')->call('search');

    Http::assertNothingSent();
});

test('it explains when the search cannot reach the internet', function () {
    AlQuranFake::offline();

    Native::visit('/quran/search')
        ->set('query', 'এক')
        ->call('search')
        ->assertSet('isSearching', false)
        ->assertSee('ইন্টারনেট সংযোগ পাওয়া যায়নি');
});
