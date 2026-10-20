<?php

namespace App\Enums;

enum Translation: string
{
    case MuhiuddinKhan = 'bn.bengali';
    case ZohurulHoque = 'bn.hoque';

    /**
     * Get the translation used until the reader picks another.
     */
    public static function default(): self
    {
        return self::MuhiuddinKhan;
    }

    /**
     * Get the translator's name as shown to the reader.
     */
    public function translator(): string
    {
        return match ($this) {
            self::MuhiuddinKhan => 'মুহিউদ্দীন খান',
            self::ZohurulHoque => 'জহুরুল হক',
        };
    }
}
