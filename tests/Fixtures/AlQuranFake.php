<?php

namespace Tests\Fixtures;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;

/**
 * A tiny, known Quran served in place of alquran.cloud and its audio CDN:
 * Al-Fatiha (ayahs 1-7) and Al-Ikhlas (ayahs 6222-6225).
 */
class AlQuranFake
{
    public const AUDIO_BYTES = 'ID3-fake-recitation';

    /**
     * Answer every alquran.cloud and CDN request from the fixtures, and fail
     * any other request, so no test ever reaches the real internet.
     *
     * @param  array<string, mixed>  $overrides  Extra or replacement URL patterns, checked first.
     */
    public static function serve(array $overrides = []): Factory
    {
        self::reset();

        // "+" keeps an override over the default for the same pattern, and checks it first.
        return Http::fake($overrides + [
            // A fresh response per request: a shared one's body is used up after the first download.
            'cdn.islamic.network/quran/audio/*' => fn () => Http::response(self::AUDIO_BYTES),
            'api.alquran.cloud/v1/surah' => self::ok([self::surah(1), self::surah(112)]),
            'api.alquran.cloud/v1/surah/1/editions/*' => fn ($request) => self::ok(self::surahEditions(1, $request->url())),
            'api.alquran.cloud/v1/surah/112/editions/*' => fn ($request) => self::ok(self::surahEditions(112, $request->url())),
            'api.alquran.cloud/v1/page/*' => self::ok(self::page()),
            'api.alquran.cloud/v1/search/*' => self::ok(self::search()),
            'api.alquran.cloud/v1/edition/format/audio' => self::ok(self::audioEditions()),
            'api.alquran.cloud/v1/meta' => self::ok(self::meta()),
        ]);
    }

    /**
     * Make every alquran.cloud and CDN request fail as if the phone were offline.
     */
    public static function offline(): Factory
    {
        self::reset();

        return Http::fake(['*' => Http::failedConnection()]);
    }

    /**
     * Start from a fresh fake, so switching between online and offline in
     * one test replaces the earlier responses instead of adding to them.
     */
    private static function reset(): void
    {
        Http::swap(new Factory(app('events')));
        Http::preventStrayRequests();
    }

    /**
     * @return array<string, mixed>
     */
    public static function surah(int $number): array
    {
        return match ($number) {
            1 => ['number' => 1, 'name' => 'سُورَةُ ٱلْفَاتِحَةِ', 'englishName' => 'Al-Faatiha', 'englishNameTranslation' => 'The Opening', 'numberOfAyahs' => 7, 'revelationType' => 'Meccan'],
            112 => ['number' => 112, 'name' => 'سُورَةُ الإِخۡلَاصِ', 'englishName' => 'Al-Ikhlaas', 'englishNameTranslation' => 'Sincerity', 'numberOfAyahs' => 4, 'revelationType' => 'Meccan'],
        };
    }

    /**
     * @param  mixed  $data
     */
    private static function ok($data): mixed
    {
        return Http::response(['code' => 200, 'status' => 'OK', 'data' => $data]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function surahEditions(int $number, string $url): array
    {
        $translation = str_contains($url, 'bn.hoque') ? 'bn.hoque' : 'bn.bengali';
        $first = $number === 1 ? 1 : 6222;
        $count = $number === 1 ? 7 : 4;

        $ayahs = fn (callable $text): array => array_map(fn (int $offset): array => [
            'number' => $first + $offset,
            'text' => $text($offset + 1),
            'numberInSurah' => $offset + 1,
            'juz' => $number === 1 ? 1 : 30,
            'page' => $number === 1 ? 1 : 604,
            'sajda' => false,
        ], range(0, $count - 1));

        $arabic = fn (int $ayah): string => $number === 112 && $ayah === 1
            ? 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ قُلْ هُوَ ٱللَّهُ أَحَدٌ'
            : "آية {$number}:{$ayah}";

        return [
            [...self::surah($number), 'ayahs' => $ayahs($arabic), 'edition' => ['identifier' => 'quran-uthmani']],
            [...self::surah($number), 'ayahs' => $ayahs(fn (int $ayah): string => "{$translation} অনুবাদ {$number}:{$ayah}"), 'edition' => ['identifier' => $translation]],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function page(): array
    {
        return [
            'number' => 1,
            'ayahs' => array_map(fn (int $ayah): array => [
                'number' => $ayah,
                'text' => "آية 1:{$ayah}",
                'surah' => self::surah(1),
                'numberInSurah' => $ayah,
                'juz' => 1,
                'page' => 1,
                'sajda' => false,
            ], range(1, 7)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function search(): array
    {
        return [
            'count' => 1,
            'total' => 1,
            'offset' => 0,
            'limit' => 20,
            'matches' => [[
                'number' => 6222,
                'text' => 'বলুন, তিনি আল্লাহ, এক',
                'edition' => ['identifier' => 'bn.bengali'],
                'surah' => self::surah(112),
                'numberInSurah' => 1,
            ]],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function audioEditions(): array
    {
        return [
            ['identifier' => 'ar.alafasy', 'language' => 'ar', 'name' => 'مشاري العفاسي', 'englishName' => 'Alafasy', 'format' => 'audio', 'type' => 'versebyverse'],
            ['identifier' => 'ar.husary', 'language' => 'ar', 'name' => 'محمود خليل الحصري', 'englishName' => 'Husary', 'format' => 'audio', 'type' => 'versebyverse'],
            ['identifier' => 'ar.alafasy-surah', 'language' => 'ar', 'name' => 'whole surah', 'englishName' => 'Whole surah only', 'format' => 'audio', 'type' => 'translation'],
            ['identifier' => 'en.walk', 'language' => 'en', 'name' => 'Ibrahim Walk', 'englishName' => 'Ibrahim Walk', 'format' => 'audio', 'type' => 'versebyverse'],
        ];
    }

    /**
     * Metadata of a two-juz Quran small enough to check by hand: juz 1 holds
     * Al-Fatiha's 7 ayahs and juz 2 starts at Al-Ikhlas.
     *
     * @return array<string, mixed>
     */
    private static function meta(): array
    {
        return [
            'surahs' => ['references' => [
                ['number' => 1, 'numberOfAyahs' => 7],
                ['number' => 2, 'numberOfAyahs' => 6214],
                ['number' => 3, 'numberOfAyahs' => 15],
            ]],
            'juzs' => ['references' => [
                ['surah' => 1, 'ayah' => 1],
                ['surah' => 2, 'ayah' => 1],
            ]],
        ];
    }
}
