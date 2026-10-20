<?php

namespace App\Services\Quran;

use App\Models\QuranResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * Talks to the alquran.cloud API. Every request to it goes through here.
 */
class AlQuranClient
{
    /**
     * Get the data of a response that never changes (a surah, a page, the
     * list of surahs), fetching it only the first time it is asked for and
     * reading the copy kept on the phone after that.
     *
     * @return array<array-key, mixed>
     *
     * @throws QuranUnavailableException
     */
    public function remember(string $path): array
    {
        $kept = QuranResponse::query()->where('path', $path)->first();

        if ($kept !== null) {
            return $kept->data;
        }

        $data = $this->get($path) ?? throw QuranUnavailableException::offline();

        QuranResponse::query()->create(['path' => $path, 'data' => $data]);

        return $data;
    }

    /**
     * Fetch the data of a response straight from the API, or null when the
     * API has nothing for the path (it answers 404 for an empty search).
     *
     * @param  array<string, scalar>  $query
     * @return array<array-key, mixed>|null
     *
     * @throws QuranUnavailableException
     */
    public function get(string $path, array $query = []): ?array
    {
        try {
            $response = $this->request()->get($path, $query);
        } catch (ConnectionException) {
            throw QuranUnavailableException::offline();
        }

        if ($response->notFound()) {
            return null;
        }

        try {
            $response->throw();
        } catch (RequestException) {
            throw QuranUnavailableException::offline();
        }

        return $response->json('data');
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(config('services.alquran.url'))
            ->acceptJson()
            ->withHeaders(['Accept-Encoding' => 'gzip'])
            ->timeout(config('services.alquran.timeout'))
            ->retry(2, 500, throw: false);
    }
}
