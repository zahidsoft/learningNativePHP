<?php

namespace App\Actions\Quran;

use App\Enums\Translation;
use App\Services\Quran\AlQuranClient;

/**
 * @phpstan-type SearchMatch array{number: int, surah: int, surahName: string, numberInSurah: int, text: string}
 */
class SearchAyahs
{
    public const PAGE_SIZE = 20;

    public function __construct(private AlQuranClient $client)
    {
        //
    }

    /**
     * Find the ayahs whose translation contains a word. Searches are not kept
     * on the phone, since every word gives different results.
     *
     * @return array{total: int, matches: list<SearchMatch>}
     */
    public function handle(string $word, Translation $translation, int $offset = 0): array
    {
        return self::results($this->fetch($word, $translation, $offset));
    }

    /**
     * Fetch the API's raw answer, or null when nothing matches. Kept apart
     * from results() so a background task can return plain data.
     *
     * @return array<array-key, mixed>|null
     */
    public function fetch(string $word, Translation $translation, int $offset = 0): ?array
    {
        $word = trim($word);

        if ($word === '') {
            return null;
        }

        return $this->client->get('search/'.rawurlencode($word)."/all/{$translation->value}", [
            'offset' => $offset,
            'limit' => self::PAGE_SIZE,
        ]);
    }

    /**
     * Turn the API's raw answer into the matches the app shows.
     *
     * @param  array<array-key, mixed>|null  $data
     * @return array{total: int, matches: list<SearchMatch>}
     */
    public static function results(?array $data): array
    {
        if ($data === null) {
            return ['total' => 0, 'matches' => []];
        }

        $matches = array_map(fn (array $match): array => [
            'number' => (int) $match['number'],
            'surah' => (int) $match['surah']['number'],
            'surahName' => (string) $match['surah']['englishName'],
            'numberInSurah' => (int) $match['numberInSurah'],
            'text' => (string) $match['text'],
        ], array_values($data['matches'] ?? []));

        return [
            'total' => (int) ($data['total'] ?? count($matches)),
            'matches' => $matches,
        ];
    }
}
