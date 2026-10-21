<?php

namespace App\Enums;

use App\Data\Letter;
use App\Services\LetterLibrary;
use Illuminate\Support\Str;

enum LearningCategory: string
{
    case English = 'english';
    case Arabic = 'arabic';
    case Numbers = 'numbers';
    case Consonants = 'consonants';
    case Vowels = 'vowels';

    /**
     * Colors cycled across letter tiles so neighbouring letters look distinct.
     *
     * @var list<string>
     */
    public const TILE_COLORS = ['#EE2F5A', '#1F7AE0', '#F9A11B', '#3AA93A', '#8B3FF0', '#D62B74'];

    /**
     * Get the display title for the category.
     */
    public function title(): string
    {
        return match ($this) {
            self::English => 'English Letters',
            self::Arabic => 'Arabic Letters',
            self::Numbers => 'Numbers',
            self::Consonants => 'Bangla Consonants',
            self::Vowels => 'Bangla Vowels',
        };
    }

    /**
     * Get the short line shown under the title.
     */
    public function subtitle(): string
    {
        return match ($this) {
            self::English => 'A to Z',
            self::Arabic => 'Alif, Ba, Ta',
            self::Numbers => '1 to 20',
            self::Consonants => 'ব্যঞ্জনবর্ণ',
            self::Vowels => 'স্বরবর্ণ',
        };
    }

    /**
     * Get the identity color of the category as a hex value.
     */
    public function color(): string
    {
        return match ($this) {
            self::English => '#1F7AE0',
            self::Arabic => '#1F8B3A',
            self::Numbers => '#D69200',
            self::Consonants => '#6B2FD6',
            self::Vowels => '#D62B74',
        };
    }

    /**
     * Get the pale tint of the identity color, used for screen backgrounds.
     */
    public function softColor(): string
    {
        return match ($this) {
            self::English => '#E8F3FF',
            self::Arabic => '#EAFAEA',
            self::Numbers => '#FFF6E0',
            self::Consonants => '#F3EDFF',
            self::Vowels => '#FFF0F6',
        };
    }

    /**
     * Get the pastel fill of the category's card on the home screen.
     */
    public function cardColor(): string
    {
        return match ($this) {
            self::English => '#D6ECFF',
            self::Arabic => '#C9F0C2',
            self::Numbers => '#FFE08A',
            self::Consonants => '#E2D6FF',
            self::Vowels => '#FFDCEC',
        };
    }

    /**
     * Get the BCP-47 language tag used when a letter is read by text-to-speech.
     */
    public function language(): string
    {
        return match ($this) {
            self::English, self::Numbers => 'en-US',
            self::Arabic => 'ar-SA',
            self::Consonants, self::Vowels => 'bn-BD',
        };
    }

    /**
     * Get the font alias (see config/native-ui.php) that draws this category's letters.
     */
    public function font(): string
    {
        return $this === self::Arabic ? 'arabic' : 'glyph';
    }

    /**
     * Get where a letter's recorded voice clip lives, relative to the public
     * directory. The file may not exist; the caller falls back to speech.
     */
    public function letterClip(Letter $letter): string
    {
        $folder = match ($this) {
            self::English => 'englishLetter',
            self::Arabic => 'arbiLetter',
            default => $this->value,
        };

        return "audio/letters/{$folder}/".($letter->clip ?? Str::slug($letter->name)).'.mp3';
    }

    /**
     * Get the tile color for the letter at the given position.
     */
    public static function tileColor(int $index): string
    {
        return self::TILE_COLORS[$index % count(self::TILE_COLORS)];
    }

    /**
     * Get every letter in the category, in learning order, as listed in
     * resources/content/letters/{category}.json.
     *
     * @return list<Letter>
     */
    public function letters(): array
    {
        return app(LetterLibrary::class)->for($this);
    }

    /**
     * Get every letter of every category, each paired with its category.
     *
     * @return list<array{category: self, letter: Letter}>
     */
    public static function allLetters(): array
    {
        $all = [];

        foreach (self::cases() as $category) {
            foreach ($category->letters() as $letter) {
                $all[] = ['category' => $category, 'letter' => $letter];
            }
        }

        return $all;
    }
}
