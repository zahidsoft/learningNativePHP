<?php

namespace App\NativeComponents\Quran;

use App\Actions\Quran\ListSurahs;
use App\Services\Quran\Bangla;
use App\Services\Quran\QuranPreferences;
use App\Services\Quran\QuranUnavailableException;
use Illuminate\Support\Str;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

/**
 * @phpstan-import-type SurahSummary from ListSurahs
 */
#[Lazy]
class SurahList extends NativeComponent
{
    /**
     * @var list<SurahSummary>
     */
    public array $surahs = [];

    public string $filter = '';

    public ?string $error = null;

    public function mount(): void
    {
        $this->load();
    }

    public function render(): Element
    {
        return $this->view('quran.surah-list', [
            'shownSurahs' => $this->shownSurahs(),
            'lastRead' => app(QuranPreferences::class)->lastRead(),
            'lastPage' => app(QuranPreferences::class)->lastPage(),
        ]);
    }

    /**
     * Fetch the list of surahs again after it failed to load.
     */
    public function load(): void
    {
        try {
            $this->surahs = app(ListSurahs::class)->handle();
            $this->error = null;
        } catch (QuranUnavailableException $exception) {
            $this->error = $exception->getMessage();
        }
    }

    /**
     * @return list<SurahSummary>
     */
    private function shownSurahs(): array
    {
        $number = Bangla::latinDigits(trim($this->filter));
        $filter = self::normalize($this->filter);

        if ($filter === '') {
            return $this->surahs;
        }

        return array_values(array_filter(
            $this->surahs,
            fn (array $surah): bool => (string) $surah['number'] === $number
                || Str::contains(self::normalize(Bangla::surahName($surah['number']).$surah['englishName'].$surah['englishNameTranslation'].$surah['name']), $filter),
        ));
    }

    /**
     * Reduce a name to the letters that matter for matching, so "ikhlas"
     * finds "Al-Ikhlaas" and "fatiha" finds "Al-Faatiha".
     */
    private static function normalize(string $text): string
    {
        $letters = (string) preg_replace('/[^\p{L}\p{N}]+/u', '', Str::lower($text));

        return (string) preg_replace('/(.)\1+/u', '$1', $letters);
    }
}
