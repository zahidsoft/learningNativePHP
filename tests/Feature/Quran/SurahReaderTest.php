<?php

use App\Enums\Translation;
use App\Models\Bookmark;
use App\Models\Khatam;
use App\NativeComponents\Quran\SurahReader;
use App\Services\Quran\AyahAudio;
use App\Services\Quran\QuranPreferences;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Native\Mobile\AsyncTask;
use Native\Mobile\Testing\Native;
use Nativephp\Speech\Events\AudioFinished;
use Tests\Fixtures\AlQuranFake;

beforeEach(function () {
    $this->storage = sys_get_temp_dir().'/learn-quran-'.uniqid();
    $this->app->useStoragePath($this->storage);

    AsyncTask::fake();
    Native::fakeBridge()->respondTo('Speech.PlayFile', ['data' => ['playing' => true]]);
});

afterEach(function () {
    File::deleteDirectory($this->storage);
});

/**
 * Count the recitation downloads sent to the audio CDN.
 */
function audioDownloads(): int
{
    return Http::recorded(fn (Request $request) => str_contains($request->url(), 'cdn.islamic.network'))->count();
}

test('it shows the arabic text with the default translation', function () {
    AlQuranFake::serve();

    Native::visit('/quran/surah/1')
        ->assertScreen(SurahReader::class)
        ->assertNavTitle('Al-Faatiha')
        ->assertSee('آية 1:1')
        ->assertSee('bn.bengali অনুবাদ 1:1')
        ->assertSee('অনুবাদ: মুহিউদ্দীন খান');
});

test('it shows the translation the reader chose', function () {
    AlQuranFake::serve();
    app(QuranPreferences::class)->setTranslation(Translation::ZohurulHoque);

    Native::visit('/quran/surah/1')
        ->assertSee('bn.hoque অনুবাদ 1:1')
        ->assertSee('অনুবাদ: জহুরুল হক');
});

test('it shows the bismillah as the heading instead of inside the first ayah', function () {
    AlQuranFake::serve();

    $screen = Native::visit('/quran/surah/112')->assertSee('بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ');

    expect($screen->get('ayahs')[0]['text'])->toBe('قُلْ هُوَ ٱللَّهُ أَحَدٌ');
});

test('it fetches a surah only the first time it is opened', function () {
    AlQuranFake::serve();

    Native::visit('/quran/surah/1');
    Native::visit('/quran/surah/1')->assertSee('آية 1:7');

    Http::assertSentCount(1);
});

test('it loads the surah when the reader retries after being offline', function () {
    AlQuranFake::offline();

    $screen = Native::visit('/quran/surah/1')->assertSee('ইন্টারনেট সংযোগ পাওয়া যায়নি');

    AlQuranFake::serve();

    $screen->tap('আবার চেষ্টা করুন')
        ->assertSet('error', null)
        ->assertSee('آية 1:1');
});

test('it opens at the requested ayah and can show the ayahs before it', function () {
    AlQuranFake::serve();

    Native::visit('/quran/surah/1/5')
        ->assertSet('startAyah', 5)
        ->assertSee('آية 1:5')
        ->assertDontSee('آية 1:4')
        ->tap('আগের আয়াতগুলো দেখুন')
        ->assertSee('آية 1:1');
});

test('it remembers where the reader is', function () {
    AlQuranFake::serve();

    Native::visit('/quran/surah/1/3');

    expect(app(QuranPreferences::class)->lastRead())->toBe(['surah' => 1, 'ayah' => 3, 'surahName' => 'Al-Faatiha']);
});

test('it saves and unsaves an ayah', function () {
    AlQuranFake::serve();

    $screen = Native::visit('/quran/surah/112')->call('toggleBookmark', 1);

    expect(Bookmark::query()->sole())
        ->ayah_number->toBe(6223)
        ->surah->toBe(112)
        ->ayah_in_surah->toBe(2);

    $screen->assertSet('bookmarked', [6223])->call('toggleBookmark', 1);

    expect(Bookmark::query()->count())->toBe(0);
});

test('it counts the whole surah in the khatam when marked as read', function () {
    AlQuranFake::serve();

    Native::visit('/quran/surah/1')
        ->tap('সূরা পড়া শেষ')
        ->assertSee('এই সূরা খতমের হিসাবে যোগ হয়েছে।');

    expect(Khatam::current()->ayahs()->count())->toBe(7);
});

test('it plays a recitation already on the phone without downloading it', function () {
    AlQuranFake::serve();
    $path = app(AyahAudio::class)->path(QuranPreferences::DEFAULT_RECITER, 3);
    File::ensureDirectoryExists(dirname($path));
    File::put($path, AlQuranFake::AUDIO_BYTES);

    Native::visit('/quran/surah/1')
        ->call('playAyah', 3)
        ->assertSet('playingAyah', 3)
        ->assertNativeCalled('Speech.PlayFile', fn (array $params) => $params['path'] === $path);

    expect(audioDownloads())->toBe(0);
});

test('it downloads a missing recitation once and plays it', function () {
    AlQuranFake::serve();

    $screen = Native::visit('/quran/surah/1')
        ->call('playAyah', 2)
        ->assertSet('playingAyah', 2)
        ->assertSet('loadingAyah', null);

    $path = app(AyahAudio::class)->path(QuranPreferences::DEFAULT_RECITER, 2);
    expect(File::get($path))->toBe(AlQuranFake::AUDIO_BYTES);

    $screen->call('stopAudio')->call('playAyah', 2)->assertSet('playingAyah', 2);

    expect(audioDownloads())->toBe(1);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://cdn.islamic.network/quran/audio/64/ar.alafasy/2.mp3');
});

test('it downloads from the reciter the reader chose', function () {
    AlQuranFake::serve();
    app(QuranPreferences::class)->setReciter('ar.husary');

    Native::visit('/quran/surah/1')->call('playAyah', 1);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/ar.husary/1.mp3'));
});

test('it plays through the surah, fetching the next ayahs ahead', function () {
    AlQuranFake::serve();

    $screen = Native::visit('/quran/surah/112')
        ->tap('পুরো সূরা শুনুন')
        ->assertSet('playingAyah', 6222)
        ->assertSet('isPlayingThrough', true);

    expect(app(AyahAudio::class)->isDownloaded('ar.alafasy', 6223))->toBeTrue()
        ->and(app(AyahAudio::class)->isDownloaded('ar.alafasy', 6224))->toBeTrue();

    $screen->emitNative(AudioFinished::class, ['path' => 'finished.mp3'])
        ->assertSet('playingAyah', 6223);
});

test('it counts the surah as read after playing through to the end', function () {
    AlQuranFake::serve();

    Native::visit('/quran/surah/112')
        ->call('playThrough', 6225)
        ->emitNative(AudioFinished::class, ['path' => 'finished.mp3'])
        ->assertSet('playingAyah', null)
        ->assertSet('isCountedInKhatam', true);
});

test('it stops after a single ayah', function () {
    AlQuranFake::serve();

    Native::visit('/quran/surah/112')
        ->call('playAyah', 6222)
        ->emitNative(AudioFinished::class, ['path' => 'finished.mp3'])
        ->assertSet('playingAyah', null);
});

test('it explains when a recitation cannot be downloaded', function () {
    AlQuranFake::serve(['cdn.islamic.network/*' => Http::failedConnection()]);

    Native::visit('/quran/surah/1')
        ->call('playAyah', 1)
        ->assertSet('playingAyah', null)
        ->assertSet('loadingAyah', null)
        ->assertSee('ইন্টারনেট সংযোগ পাওয়া যায়নি');

    expect(app(AyahAudio::class)->isDownloaded('ar.alafasy', 1))->toBeFalse();
});
