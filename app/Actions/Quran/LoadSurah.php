<?php

namespace App\Actions\Quran;

use App\Enums\Translation;
use App\Services\Quran\AlQuranClient;
use App\Services\Quran\Mushaf;

/**
 * @phpstan-import-type SurahSummary from ListSurahs
 *
 * @phpstan-type SurahAyah array{number: int, numberInSurah: int, text: string, translation: string, juz: int, page: int, sajda: bool}
 */
class LoadSurah
{
    public function __construct(private AlQuranClient $client)
    {
        //
    }

    /**
     * Load a surah's Arabic text with the chosen Bangla translation beside
     * each ayah. The pair is fetched in one request and kept on the phone.
     *
     * @return array{surah: SurahSummary, ayahs: list<SurahAyah>}
     */
    public function handle(int $number, Translation $translation): array
    {
        $number = Mushaf::clamp($number, Mushaf::SURAH_COUNT);

        [$arabic, $bangla] = $this->client->remember("surah/{$number}/editions/quran-uthmani,{$translation->value}");

        $ayahs = [];

        foreach ($arabic['ayahs'] as $position => $ayah) {
            $ayahs[] = [
                'number' => (int) $ayah['number'],
                'numberInSurah' => (int) $ayah['numberInSurah'],
                'text' => Mushaf::withoutBismillah((string) $ayah['text'], $number, (int) $ayah['numberInSurah']),
                'translation' => (string) ($bangla['ayahs'][$position]['text'] ?? ''),
                'juz' => (int) $ayah['juz'],
                'page' => (int) $ayah['page'],
                'sajda' => $ayah['sajda'] !== false,
            ];
        }

        return [
            'surah' => ListSurahs::summary($arabic),
            'ayahs' => $ayahs,
        ];
    }
}
