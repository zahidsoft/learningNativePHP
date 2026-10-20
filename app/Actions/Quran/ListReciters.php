<?php

namespace App\Actions\Quran;

use App\Services\Quran\AlQuranClient;

/**
 * @phpstan-type Reciter array{identifier: string, name: string, englishName: string}
 */
class ListReciters
{
    public function __construct(private AlQuranClient $client)
    {
        //
    }

    /**
     * List the reciters whose recitation is published ayah by ayah in Arabic.
     *
     * @return list<Reciter>
     */
    public function handle(): array
    {
        $editions = array_filter(
            $this->client->remember('edition/format/audio'),
            fn (array $edition): bool => $edition['language'] === 'ar' && $edition['type'] === 'versebyverse',
        );

        return array_values(array_map(fn (array $edition): array => [
            'identifier' => (string) $edition['identifier'],
            'name' => (string) $edition['name'],
            'englishName' => (string) $edition['englishName'],
        ], $editions));
    }
}
