<?php

namespace App\Actions\Quran;

use App\Services\Quran\AlQuranClient;

/**
 * @phpstan-type SurahSummary array{number: int, name: string, englishName: string, englishNameTranslation: string, revelationType: string, numberOfAyahs: int}
 */
class ListSurahs
{
    public function __construct(private AlQuranClient $client)
    {
        //
    }

    /**
     * List all 114 surahs in order.
     *
     * @return list<SurahSummary>
     */
    public function handle(): array
    {
        return array_map(
            fn (array $surah): array => self::summary($surah),
            array_values($this->client->remember('surah')),
        );
    }

    /**
     * Keep only the fields of a surah the app shows.
     *
     * @param  array<string, mixed>  $surah
     * @return SurahSummary
     */
    public static function summary(array $surah): array
    {
        return [
            'number' => (int) $surah['number'],
            'name' => (string) $surah['name'],
            'englishName' => (string) $surah['englishName'],
            'englishNameTranslation' => (string) $surah['englishNameTranslation'],
            'revelationType' => (string) $surah['revelationType'],
            'numberOfAyahs' => (int) $surah['numberOfAyahs'],
        ];
    }
}
