<?php

namespace App\Enums;

use App\Data\Letter;
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
     * The recording of each Arabic letter in public/audio/letters/arbiLetter,
     * whose files are numbered in the order a Bangladeshi qaida teaches them.
     *
     * @var array<string, string>
     */
    private const ARABIC_CLIPS = [
        'ا' => '01-alif', 'ب' => '02-ba', 'ت' => '03-taa', 'ث' => '04-tha', 'ج' => '05-jeem',
        'ح' => '06-haa', 'خ' => '07-khaa', 'د' => '08-dal', 'ذ' => '09-dhal', 'ر' => '10-raa',
        'ز' => '11-jaa', 'س' => '12-seen', 'ش' => '13-sheen', 'ص' => '14-saad', 'ض' => '15-dhaad',
        'ط' => '16-toa', 'ظ' => '17-dhaa', 'ع' => '18-ain', 'غ' => '19-ghain', 'ف' => '20-faa',
        'ق' => '21-qaaf', 'ك' => '22-kaaf', 'ل' => '23-laam', 'م' => '24-meem', 'ن' => '25-noon',
        'و' => '26-waw', 'ه' => '27-ha', 'ء' => '28-hamza', 'ي' => '29-yaa',
    ];

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
        return match ($this) {
            self::English => 'audio/letters/englishLetter/'.Str::slug($letter->name).'.mp3',
            self::Arabic => 'audio/letters/arbiLetter/'.(self::ARABIC_CLIPS[$letter->character] ?? Str::slug($letter->name)).'.mp3',
            default => "audio/letters/{$this->value}/".Str::slug($letter->name).'.mp3',
        };
    }

    /**
     * Get the tile color for the letter at the given position.
     */
    public static function tileColor(int $index): string
    {
        return self::TILE_COLORS[$index % count(self::TILE_COLORS)];
    }

    /**
     * Get every letter in the category, in learning order.
     *
     * @return list<Letter>
     */
    public function letters(): array
    {
        return match ($this) {
            self::English => self::parse('A:Ay:Apple|B:Bee:Ball|C:See:Cat|D:Dee:Duck|E:Ee:Egg|F:Ef:Fish|G:Jee:Goat|H:Aitch:Hat|I:Eye:Ice cream|J:Jay:Jug|K:Kay:Kite|L:El:Lion|M:Em:Moon|N:En:Nest|O:Oh:Owl|P:Pee:Pencil|Q:Queue:Queen|R:Ar:Rain|S:Es:Sun|T:Tee:Tree|U:You:Umbrella|V:Vee:Van|W:Double-u:Water|X:Ex:Box|Y:Wy:Yarn|Z:Zed:Zebra'),
            self::Arabic => self::parse('ا:Alif|ب:Ba|ت:Ta|ث:Tha|ج:Jim|ح:Ha|خ:Kha|د:Dal|ذ:Dhal|ر:Ra|ز:Zay|س:Sin|ش:Shin|ص:Sad|ض:Dad|ط:Taa|ظ:Zaa|ع:Ain|غ:Ghain|ف:Fa|ق:Qaf|ك:Kaf|ل:Lam|م:Mim|ن:Nun|ه:Haa|و:Waw|ء:Hamza|ي:Ya', 'Arabic alphabet'),
            self::Numbers => self::parse('1:One:১|2:Two:২|3:Three:৩|4:Four:৪|5:Five:৫|6:Six:৬|7:Seven:৭|8:Eight:৮|9:Nine:৯|10:Ten:১০|11:Eleven:১১|12:Twelve:১২|13:Thirteen:১৩|14:Fourteen:১৪|15:Fifteen:১৫|16:Sixteen:১৬|17:Seventeen:১৭|18:Eighteen:১৮|19:Nineteen:১৯|20:Twenty:২০'),
            self::Consonants => self::parse('ক:ko|খ:kho|গ:go|ঘ:gho|ঙ:ungo|চ:cho|ছ:chho|জ:borgio jo|ঝ:jho|ঞ:ingo|ট:to|ঠ:tho|ড:do|ঢ:dho|ণ:murdhonno no|ত:to|থ:tho|দ:do|ধ:dho|ন:donto no|প:po|ফ:pho|ব:bo|ভ:bho|ম:mo|য:ontosto jo|র:ro|ল:lo|শ:talobbo sho|ষ:murdhonno sho|স:donto so|হ:ho', 'ব্যঞ্জনবর্ণ'),
            self::Vowels => self::parse('অ:o|আ:a|ই:i|ঈ:dirgho i|উ:u|ঊ:dirgho u|ঋ:ri|এ:e|ঐ:oi|ও:o|ঔ:ou', 'স্বরবর্ণ'),
        };
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

    /**
     * Expand the compact "character:name:word" rows into letters.
     *
     * @return list<Letter>
     */
    private static function parse(string $rows, string $fallbackWord = ''): array
    {
        return array_map(function (string $row) use ($fallbackWord): Letter {
            $parts = explode(':', $row);

            return new Letter($parts[0], $parts[1], $parts[2] ?? $fallbackWord);
        }, explode('|', $rows));
    }
}
