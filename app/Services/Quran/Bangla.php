<?php

namespace App\Services\Quran;

/**
 * Bangla wording for the Quran section, which is read in Bangla.
 */
class Bangla
{
    private const DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    /**
     * Write a number with Bangla digits: 114 becomes ১১৪.
     */
    public static function number(int|string $number): string
    {
        return strtr((string) $number, array_combine(range(0, 9), self::DIGITS));
    }

    /**
     * Turn Bangla digits typed by the reader into Latin ones: ১১২ becomes 112.
     */
    public static function latinDigits(string $text): string
    {
        return strtr($text, array_flip(array_combine(range(0, 9), self::DIGITS)));
    }

    /**
     * Name where a surah was revealed, from the API's "Meccan" / "Medinan".
     */
    public static function revelation(string $revelationType): string
    {
        return $revelationType === 'Medinan' ? 'মাদানী' : 'মক্কী';
    }
}
