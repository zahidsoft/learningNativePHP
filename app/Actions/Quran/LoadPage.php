<?php

namespace App\Actions\Quran;

use App\Services\Quran\AlQuranClient;
use App\Services\Quran\Mushaf;

/**
 * @phpstan-type PageAyah array{number: int, numberInSurah: int, surah: int, surahName: string, text: string, juz: int}
 */
class LoadPage
{
    public function __construct(private AlQuranClient $client)
    {
        //
    }

    /**
     * Load one page of the muṣḥaf in the Uthmani script.
     *
     * @return array{page: int, juz: int, surahNames: list<string>, ayahs: list<PageAyah>}
     */
    public function handle(int $page): array
    {
        $page = Mushaf::clamp($page, Mushaf::PAGE_COUNT);
        $data = $this->client->remember("page/{$page}/quran-uthmani");

        $ayahs = array_map(fn (array $ayah): array => [
            'number' => (int) $ayah['number'],
            'numberInSurah' => (int) $ayah['numberInSurah'],
            'surah' => (int) $ayah['surah']['number'],
            'surahName' => (string) $ayah['surah']['name'],
            'text' => Mushaf::withoutBismillah((string) $ayah['text'], (int) $ayah['surah']['number'], (int) $ayah['numberInSurah']),
            'juz' => (int) $ayah['juz'],
        ], array_values($data['ayahs']));

        return [
            'page' => $page,
            'juz' => $ayahs[0]['juz'] ?? 1,
            'surahNames' => array_values(array_unique(array_column($ayahs, 'surahName'))),
            'ayahs' => $ayahs,
        ];
    }
}
