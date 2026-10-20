<?php

namespace App\Services\Quran;

/**
 * Bangla wording for the Quran section, which is read in Bangla.
 */
class Bangla
{
    private const DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    /**
     * The surah names as they are commonly written in Bangla, in surah order.
     * The API only gives Arabic and English names.
     *
     * @var list<string>
     */
    private const SURAH_NAMES = [
        'আল-ফাতিহা', 'আল-বাকারা', 'আলে ইমরান', 'আন-নিসা', 'আল-মায়িদা', 'আল-আনআম', 'আল-আরাফ', 'আল-আনফাল', 'আত-তাওবা', 'ইউনুস',
        'হুদ', 'ইউসুফ', 'আর-রাদ', 'ইবরাহীম', 'আল-হিজর', 'আন-নাহল', 'আল-ইসরা', 'আল-কাহফ', 'মারইয়াম', 'ত্বা-হা',
        'আল-আম্বিয়া', 'আল-হাজ্জ', 'আল-মুমিনূন', 'আন-নূর', 'আল-ফুরকান', 'আশ-শুআরা', 'আন-নামল', 'আল-কাসাস', 'আল-আনকাবূত', 'আর-রূম',
        'লুকমান', 'আস-সাজদা', 'আল-আহযাব', 'সাবা', 'ফাতির', 'ইয়াসীন', 'আস-সাফফাত', 'সোয়াদ', 'আয-যুমার', 'আল-মুমিন',
        'হা-মীম আস-সাজদা', 'আশ-শূরা', 'আয-যুখরুফ', 'আদ-দুখান', 'আল-জাসিয়া', 'আল-আহকাফ', 'মুহাম্মাদ', 'আল-ফাতহ', 'আল-হুজুরাত', 'ক্বাফ',
        'আয-যারিয়াত', 'আত-তূর', 'আন-নাজম', 'আল-কামার', 'আর-রাহমান', 'আল-ওয়াকিয়া', 'আল-হাদীদ', 'আল-মুজাদালা', 'আল-হাশর', 'আল-মুমতাহিনা',
        'আস-সফ', 'আল-জুমুআ', 'আল-মুনাফিকূন', 'আত-তাগাবুন', 'আত-তালাক', 'আত-তাহরীম', 'আল-মুলক', 'আল-কলম', 'আল-হাক্কাহ', 'আল-মাআরিজ',
        'নূহ', 'আল-জ্বিন', 'আল-মুযযাম্মিল', 'আল-মুদ্দাসসির', 'আল-কিয়ামাহ', 'আল-ইনসান', 'আল-মুরসালাত', 'আন-নাবা', 'আন-নাযিয়াত', 'আবাসা',
        'আত-তাকভীর', 'আল-ইনফিতার', 'আল-মুতাফফিফীন', 'আল-ইনশিকাক', 'আল-বুরূজ', 'আত-তারিক', 'আল-আলা', 'আল-গাশিয়াহ', 'আল-ফাজর', 'আল-বালাদ',
        'আশ-শামস', 'আল-লাইল', 'আদ-দুহা', 'আশ-শারহ', 'আত-তীন', 'আল-আলাক', 'আল-কদর', 'আল-বাইয়্যিনাহ', 'আয-যিলযাল', 'আল-আদিয়াত',
        'আল-কারিয়াহ', 'আত-তাকাসুর', 'আল-আসর', 'আল-হুমাযাহ', 'আল-ফীল', 'কুরাইশ', 'আল-মাউন', 'আল-কাওসার', 'আল-কাফিরূন', 'আন-নাসর',
        'আল-লাহাব', 'আল-ইখলাস', 'আল-ফালাক', 'আন-নাস',
    ];

    /**
     * Write a number with Bangla digits: 114 becomes ১১৪.
     */
    public static function number(int|string $number): string
    {
        return strtr((string) $number, array_combine(range(0, 9), self::DIGITS));
    }

    /**
     * Get the Bangla name of a surah, such as আল-ফাতিহা for surah 1.
     */
    public static function surahName(int $surah): string
    {
        return self::SURAH_NAMES[$surah - 1] ?? self::number($surah);
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
