<?php

use App\Services\Quran\AyahAudio;
use App\Services\Quran\QuranUnavailableException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\Fixtures\AlQuranFake;

beforeEach(function () {
    $this->storage = sys_get_temp_dir().'/learn-quran-'.uniqid();
    $this->app->useStoragePath($this->storage);
});

afterEach(function () {
    File::deleteDirectory($this->storage);
});

test('it downloads a recitation once and reuses it after', function () {
    AlQuranFake::serve();
    $audio = app(AyahAudio::class);

    $first = $audio->download('ar.alafasy', 10);
    $second = $audio->download('ar.alafasy', 10);

    expect($second)->toBe($first)
        ->and(File::get($first))->toBe(AlQuranFake::AUDIO_BYTES);
    Http::assertSentCount(1);
});

test('it tries another bitrate when the reciter lacks the first', function () {
    AlQuranFake::serve(['cdn.islamic.network/quran/audio/64/*' => Http::response('', 403)]);

    $path = app(AyahAudio::class)->download('ar.abdulsamad', 1);

    expect(File::get($path))->toBe(AlQuranFake::AUDIO_BYTES);
    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/audio/128/ar.abdulsamad/1.mp3'));
});

test('it leaves no half downloaded file behind when the connection drops', function () {
    AlQuranFake::offline();
    $audio = app(AyahAudio::class);

    expect(fn () => $audio->download('ar.alafasy', 10))->toThrow(QuranUnavailableException::class);

    $path = $audio->path('ar.alafasy', 10);
    expect(file_exists($path))->toBeFalse()
        ->and(file_exists("{$path}.part"))->toBeFalse()
        ->and(file_exists("{$path}.lock"))->toBeFalse();
});

test('it takes over a lock left by a download that died with the app', function () {
    AlQuranFake::serve();
    $audio = app(AyahAudio::class);
    $lock = $audio->path('ar.alafasy', 10).'.lock';
    File::ensureDirectoryExists(dirname($lock));
    File::put($lock, '');
    touch($lock, time() - 600);

    expect(File::get($audio->download('ar.alafasy', 10)))->toBe(AlQuranFake::AUDIO_BYTES);
});

test('it reports the space each reciter takes and frees it', function () {
    AlQuranFake::serve();
    $audio = app(AyahAudio::class);
    $audio->download('ar.alafasy', 1);
    $audio->download('ar.alafasy', 2);
    $audio->download('ar.husary', 1);

    expect($audio->downloadedSizes())->toBe([
        'ar.alafasy' => 2 * strlen(AlQuranFake::AUDIO_BYTES),
        'ar.husary' => strlen(AlQuranFake::AUDIO_BYTES),
    ]);

    $audio->deleteReciter('ar.alafasy');

    expect($audio->downloadedSizes())->toBe(['ar.husary' => strlen(AlQuranFake::AUDIO_BYTES)]);
});
