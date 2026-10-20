<?php

use App\Enums\Translation;
use App\Services\Quran\AyahAudio;
use App\Services\Quran\QuranPreferences;
use Illuminate\Support\Facades\File;
use Native\Mobile\AsyncTask;
use Native\Mobile\Testing\Native;
use Tests\Fixtures\AlQuranFake;

beforeEach(function () {
    $this->storage = sys_get_temp_dir().'/learn-quran-'.uniqid();
    $this->app->useStoragePath($this->storage);

    AsyncTask::fake();
    AlQuranFake::serve();
});

afterEach(function () {
    File::deleteDirectory($this->storage);
});

test('it uses muhiuddin khan and alafasy until the reader chooses', function () {
    $preferences = app(QuranPreferences::class);

    expect($preferences->translation())->toBe(Translation::MuhiuddinKhan)
        ->and($preferences->reciter())->toBe('ar.alafasy');
});

test('it lists only arabic reciters published ayah by ayah', function () {
    Native::visit('/quran/settings')
        ->assertSee('Alafasy')
        ->assertSee('Husary')
        ->assertDontSee('Ibrahim Walk')
        ->assertDontSee('Whole surah only');
});

test('it saves the chosen translation', function () {
    Native::visit('/quran/settings')->tap('জহুরুল হক');

    expect(app(QuranPreferences::class)->translation())->toBe(Translation::ZohurulHoque);
});

test('it saves the chosen reciter', function () {
    Native::visit('/quran/settings')->tap('Husary');

    expect(app(QuranPreferences::class)->reciter())->toBe('ar.husary');
});

test('it ignores a reciter that is not in the list', function () {
    Native::visit('/quran/settings')->call('chooseReciter', 'en.walk');

    expect(app(QuranPreferences::class)->reciter())->toBe('ar.alafasy');
});

test('it lets the reader hear a reciter before choosing', function () {
    Native::fakeBridge()->respondTo('Speech.PlayFile', ['data' => ['playing' => true]]);

    Native::visit('/quran/settings')
        ->call('preview', 'ar.husary')
        ->assertSet('previewing', null)
        ->assertNativeCalled('Speech.PlayFile', fn (array $params) => str_ends_with(str_replace('\\', '/', $params['path']), 'quran-audio/ar.husary/1.mp3'));

    expect(app(QuranPreferences::class)->reciter())->toBe('ar.alafasy');
});

test('it shows and deletes the downloaded recitations of a reciter', function () {
    app(AyahAudio::class)->download('ar.husary', 1);

    Native::visit('/quran/settings')
        ->assertSee('ডাউনলোড করা তিলাওয়াত')
        ->call('deleteDownloads', 'ar.husary')
        ->assertSee('এখনো কোনো তিলাওয়াত ডাউনলোড হয়নি।');

    expect(app(AyahAudio::class)->isDownloaded('ar.husary', 1))->toBeFalse();
});
