<?php

namespace App\NativeComponents\Quran;

use App\Actions\Quran\ListSurahs;
use App\Actions\Quran\LoadSurah;
use App\Actions\Quran\RecordAyahsRead;
use App\Actions\Quran\ToggleBookmark;
use App\Concerns\PlaysAyahAudio;
use App\Models\Bookmark;
use App\Services\Quran\QuranPreferences;
use App\Services\Quran\QuranUnavailableException;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

/**
 * @phpstan-import-type SurahSummary from ListSurahs
 * @phpstan-import-type SurahAyah from LoadSurah
 */
#[Lazy]
class SurahReader extends NativeComponent
{
    use PlaysAyahAudio;

    public int $number = 1;

    /**
     * The ayah to open at, counted within the surah.
     */
    public int $startAyah = 1;

    /**
     * @var SurahSummary|null
     */
    public ?array $surah = null;

    /**
     * @var list<SurahAyah>
     */
    public array $ayahs = [];

    /**
     * Numbers of the ayahs of this surah the reader has saved.
     *
     * @var list<int>
     */
    public array $bookmarked = [];

    public bool $isCountedInKhatam = false;

    public ?string $error = null;

    public function mount(): void
    {
        $this->number = (int) $this->param('number', 1);
        $this->startAyah = max(1, (int) $this->param('ayah', 1));

        $this->load();
    }

    public function render(): Element
    {
        return $this->view('quran.surah-reader', [
            'translation' => app(QuranPreferences::class)->translation(),
        ]);
    }

    /**
     * Load the surah, from the phone when it was opened before.
     */
    public function load(): void
    {
        try {
            $loaded = app(LoadSurah::class)->handle($this->number, app(QuranPreferences::class)->translation());
        } catch (QuranUnavailableException $exception) {
            $this->error = $exception->getMessage();

            return;
        }

        $this->error = null;
        $this->surah = $loaded['surah'];
        $this->ayahs = $loaded['ayahs'];
        $this->number = $loaded['surah']['number'];
        $this->startAyah = min($this->startAyah, count($this->ayahs));
        $this->bookmarked = array_values(Bookmark::query()
            ->whereIn('ayah_number', array_column($this->ayahs, 'number'))
            ->get()
            ->map(fn (Bookmark $bookmark): int => $bookmark->ayah_number)
            ->all());

        $this->rememberPlace($this->startAyah);
    }

    /**
     * Show the ayahs before the one the surah was opened at.
     */
    public function showFromStart(): void
    {
        $this->startAyah = 1;
    }

    /**
     * Save or unsave the ayah at the given position.
     */
    public function toggleBookmark(int $position): void
    {
        $ayah = $this->ayahs[$position] ?? null;

        if ($ayah === null || $this->surah === null) {
            return;
        }

        $isSaved = app(ToggleBookmark::class)->handle($ayah['number'], $this->number, $ayah['numberInSurah'], $this->surah['englishName']);

        $this->bookmarked = $isSaved
            ? [...$this->bookmarked, $ayah['number']]
            : array_values(array_diff($this->bookmarked, [$ayah['number']]));
    }

    /**
     * Count every ayah of the surah as read in the khatam in progress.
     */
    public function markAsRead(): void
    {
        app(RecordAyahsRead::class)->handle(array_map(
            fn (array $ayah): array => ['number' => $ayah['number'], 'juz' => $ayah['juz']],
            $this->ayahs,
        ));

        $this->isCountedInKhatam = true;
    }

    /**
     * @return list<int>
     */
    protected function playableAyahs(): array
    {
        return array_column($this->ayahs, 'number');
    }

    protected function ayahStarted(int $number): void
    {
        $position = array_search($number, $this->playableAyahs(), true);

        if ($position !== false) {
            $this->rememberPlace($this->ayahs[$position]['numberInSurah']);
        }
    }

    protected function playedThroughToEnd(): void
    {
        $this->markAsRead();
    }

    private function rememberPlace(int $ayahInSurah): void
    {
        if ($this->surah !== null) {
            app(QuranPreferences::class)->setLastRead($this->number, $ayahInSurah, $this->surah['englishName']);
        }
    }
}
