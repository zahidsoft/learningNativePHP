<?php

namespace App\Services\Quran;

/**
 * Fixed facts about the 604-page Madani muṣḥaf the API numbers its pages by.
 */
class Mushaf
{
    public const PAGE_COUNT = 604;

    public const AYAH_COUNT = 6236;

    public const SURAH_COUNT = 114;

    public const JUZ_COUNT = 30;

    /**
     * The Uthmani text prefixes the first ayah of most surahs with this line.
     */
    public const BISMILLAH = 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ';

    /**
     * The page each juz starts on, in juz order. Juz 7 and 11 begin part way
     * down a page, so that page is shared with the juz before.
     *
     * @var list<int>
     */
    private const JUZ_START_PAGES = [
        1, 22, 42, 62, 82, 102, 121, 142, 162, 182,
        201, 222, 242, 262, 282, 302, 322, 342, 362, 382,
        402, 422, 442, 462, 482, 502, 522, 542, 562, 582,
    ];

    /**
     * Get the page the given juz starts on.
     */
    public static function juzStartPage(int $juz): int
    {
        return self::JUZ_START_PAGES[self::clamp($juz, self::JUZ_COUNT) - 1];
    }

    /**
     * Determine whether a surah opens with the bismillah line. Al-Fatiha
     * counts it as its first ayah and At-Tawba has none.
     */
    public static function opensWithBismillah(int $surah): bool
    {
        return $surah !== 1 && $surah !== 9;
    }

    /**
     * Take the bismillah off the first ayah of a surah, since it is shown as
     * the surah's heading rather than as part of the ayah.
     */
    public static function withoutBismillah(string $text, int $surah, int $numberInSurah): string
    {
        if ($numberInSurah !== 1 || ! self::opensWithBismillah($surah) || ! str_starts_with($text, self::BISMILLAH)) {
            return $text;
        }

        return trim(substr($text, strlen(self::BISMILLAH)));
    }

    /**
     * Write an ayah number the way the muṣḥaf marks it: ﴿١٢﴾.
     */
    public static function ayahMarker(int $number): string
    {
        return '﴿'.strtr((string) $number, array_combine(range(0, 9), ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'])).'﴾';
    }

    /**
     * Keep a number between 1 and the given maximum.
     */
    public static function clamp(int $number, int $maximum): int
    {
        return max(1, min($maximum, $number));
    }
}
