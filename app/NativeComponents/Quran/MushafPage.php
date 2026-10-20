<?php

namespace App\NativeComponents\Quran;

use App\Actions\Quran\LoadPage;
use App\Actions\Quran\RecordAyahsRead;
use App\Services\Quran\Mushaf;
use App\Services\Quran\QuranPreferences;
use App\Services\Quran\QuranUnavailableException;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

/**
 * @phpstan-import-type PageAyah from LoadPage
 */
#[Lazy]
class MushafPage extends NativeComponent
{
    public int $page = 1;

    public int $juz = 1;

    /**
     * @var list<PageAyah>
     */
    public array $ayahs = [];

    public bool $isPickingJuz = false;

    /**
     * Pages counted in the khatam while this screen was open.
     *
     * @var list<int>
     */
    public array $countedPages = [];

    public ?string $error = null;

    public function mount(): void
    {
        $this->open((int) $this->param('page', app(QuranPreferences::class)->lastPage()));
    }

    public function render(): Element
    {
        return $this->view('quran.mushaf-page', [
            'sections' => $this->surahSections(),
            'surahNumbers' => array_values(array_unique(array_column($this->ayahs, 'surah'))),
            'isFirstPage' => $this->page === 1,
            'isLastPage' => $this->page === Mushaf::PAGE_COUNT,
        ]);
    }

    /**
     * Turn to the next page, counting the one just read in the khatam.
     */
    public function nextPage(): void
    {
        $this->countPageRead();

        if ($this->page < Mushaf::PAGE_COUNT) {
            $this->open($this->page + 1);
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->open($this->page - 1);
        }
    }

    public function toggleJuzPicker(): void
    {
        $this->isPickingJuz = ! $this->isPickingJuz;
    }

    public function openJuz(int $juz): void
    {
        $this->isPickingJuz = false;

        $this->open(Mushaf::juzStartPage($juz));
    }

    /**
     * Count every ayah on this page as read in the khatam in progress.
     */
    public function countPageRead(): void
    {
        if ($this->ayahs === [] || in_array($this->page, $this->countedPages, true)) {
            return;
        }

        app(RecordAyahsRead::class)->handle(array_map(
            fn (array $ayah): array => ['number' => $ayah['number'], 'juz' => $ayah['juz']],
            $this->ayahs,
        ));

        $this->countedPages[] = $this->page;
    }

    /**
     * Load the current page again after it failed to load.
     */
    public function retry(): void
    {
        $this->open($this->page);
    }

    private function open(int $page): void
    {
        $this->page = Mushaf::clamp($page, Mushaf::PAGE_COUNT);

        try {
            $loaded = app(LoadPage::class)->handle($this->page);
        } catch (QuranUnavailableException $exception) {
            $this->ayahs = [];
            $this->error = $exception->getMessage();

            return;
        }

        $this->error = null;
        $this->ayahs = $loaded['ayahs'];
        $this->juz = $loaded['juz'];

        app(QuranPreferences::class)->setLastPage($this->page);
    }

    /**
     * Split the page where a new surah starts, so each start gets its heading.
     *
     * @return list<array{surah: int, surahName: string, startsSurah: bool, ayahs: list<PageAyah>}>
     */
    private function surahSections(): array
    {
        $sections = [];

        foreach ($this->ayahs as $ayah) {
            $last = array_key_last($sections);

            if ($last === null || $sections[$last]['surah'] !== $ayah['surah']) {
                $sections[] = [
                    'surah' => $ayah['surah'],
                    'surahName' => $ayah['surahName'],
                    'startsSurah' => $ayah['numberInSurah'] === 1,
                    'ayahs' => [],
                ];
                $last = array_key_last($sections);
            }

            $sections[$last]['ayahs'][] = $ayah;
        }

        return $sections;
    }
}
