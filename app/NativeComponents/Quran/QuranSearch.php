<?php

namespace App\NativeComponents\Quran;

use App\Actions\Quran\SearchAyahs;
use App\Enums\Translation;
use App\Services\Quran\QuranPreferences;
use Native\Mobile\AsyncTask;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;
use Throwable;

/**
 * @phpstan-import-type SearchMatch from SearchAyahs
 */
class QuranSearch extends NativeComponent
{
    public string $query = '';

    /**
     * The word the shown results are for.
     */
    public string $searchedFor = '';

    /**
     * @var list<SearchMatch>
     */
    public array $matches = [];

    public int $total = 0;

    public bool $isSearching = false;

    public ?string $error = null;

    public function render(): Element
    {
        return $this->view('quran.quran-search', [
            'translation' => app(QuranPreferences::class)->translation(),
            'hasMore' => count($this->matches) < $this->total,
        ]);
    }

    /**
     * Search the chosen translation for the typed word.
     */
    public function search(): void
    {
        $this->searchedFor = trim($this->query);
        $this->matches = [];
        $this->total = 0;

        if ($this->searchedFor !== '') {
            $this->fetch(0);
        }
    }

    /**
     * Fetch the next page of results for the same word.
     */
    public function loadMore(): void
    {
        if (! $this->isSearching && count($this->matches) < $this->total) {
            $this->fetch(count($this->matches));
        }
    }

    private function fetch(int $offset): void
    {
        $word = $this->searchedFor;
        $translation = app(QuranPreferences::class)->translation()->value;

        $this->isSearching = true;
        $this->error = null;

        AsyncTask::dispatch(static fn (): ?array => app(SearchAyahs::class)->fetch($word, Translation::from($translation), $offset))
            ->finished(fn (?array $data) => $this->receiveResults($word, SearchAyahs::results($data)))
            ->failed(function (Throwable $exception): void {
                $this->isSearching = false;
                $this->error = $exception->getMessage();
            });
    }

    /**
     * Add a page of results, unless the reader has searched for another word since.
     *
     * @param  array{total: int, matches: list<SearchMatch>}  $results
     */
    private function receiveResults(string $word, array $results): void
    {
        if ($word !== $this->searchedFor) {
            return;
        }

        $this->isSearching = false;
        $this->total = $results['total'];
        $this->matches = [...$this->matches, ...$results['matches']];
    }
}
