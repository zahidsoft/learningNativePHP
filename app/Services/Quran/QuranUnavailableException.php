<?php

namespace App\Services\Quran;

use RuntimeException;

/**
 * The Quran text or audio is not on the phone yet and could not be fetched,
 * usually because there is no internet connection.
 */
class QuranUnavailableException extends RuntimeException
{
    public static function offline(): self
    {
        return new self('ইন্টারনেট সংযোগ পাওয়া যায়নি। সংযোগ চালু করে আবার চেষ্টা করুন।');
    }
}
