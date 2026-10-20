<?php

namespace App\Services\Quran;

use App\Enums\Translation;
use App\Models\Preference;

/**
 * The reader's Quran settings and where they stopped reading.
 */
class QuranPreferences
{
    public const DEFAULT_RECITER = 'ar.alafasy';

    private const TRANSLATION = 'quran.translation';

    private const RECITER = 'quran.reciter';

    private const LAST_READ = 'quran.last_read';

    private const LAST_PAGE = 'quran.last_page';

    public function translation(): Translation
    {
        return Translation::tryFrom((string) Preference::read(self::TRANSLATION)) ?? Translation::default();
    }

    public function setTranslation(Translation $translation): void
    {
        Preference::write(self::TRANSLATION, $translation->value);
    }

    /**
     * Get the identifier of the chosen reciter's audio edition, such as "ar.alafasy".
     */
    public function reciter(): string
    {
        return (string) Preference::read(self::RECITER, self::DEFAULT_RECITER);
    }

    public function setReciter(string $reciter): void
    {
        Preference::write(self::RECITER, $reciter);
    }

    /**
     * Get where the reader stopped, or null before they read anything.
     *
     * @return array{surah: int, ayah: int, surahName: string}|null
     */
    public function lastRead(): ?array
    {
        $lastRead = Preference::read(self::LAST_READ);

        if (! is_array($lastRead) || ! isset($lastRead['surah'], $lastRead['ayah'], $lastRead['surahName'])) {
            return null;
        }

        return [
            'surah' => (int) $lastRead['surah'],
            'ayah' => (int) $lastRead['ayah'],
            'surahName' => (string) $lastRead['surahName'],
        ];
    }

    public function setLastRead(int $surah, int $ayah, string $surahName): void
    {
        Preference::write(self::LAST_READ, ['surah' => $surah, 'ayah' => $ayah, 'surahName' => $surahName]);
    }

    /**
     * Get the muṣḥaf page the reader last opened.
     */
    public function lastPage(): int
    {
        return Mushaf::clamp((int) Preference::read(self::LAST_PAGE, 1), Mushaf::PAGE_COUNT);
    }

    public function setLastPage(int $page): void
    {
        Preference::write(self::LAST_PAGE, $page);
    }
}
