<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncLearningAssetsCommand extends Command
{
    /**
     * Letter -> spoken name, matching the "english" category in
     * resources/js/features/learning/data/categories.ts. Used to slug the
     * letter-name audio clips the same way useLetterSound() looks them up.
     *
     * @var array<string, string>
     */
    private const LETTER_NAMES = [
        'a' => 'Ay', 'b' => 'Bee', 'c' => 'See', 'd' => 'Dee', 'e' => 'Ee', 'f' => 'Ef',
        'g' => 'Jee', 'h' => 'Aitch', 'i' => 'Eye', 'j' => 'Jay', 'k' => 'Kay', 'l' => 'El',
        'm' => 'Em', 'n' => 'En', 'o' => 'Oh', 'p' => 'Pee', 'q' => 'Queue', 'r' => 'Ar',
        's' => 'Es', 't' => 'Tee', 'u' => 'You', 'v' => 'Vee', 'w' => 'Double-u', 'x' => 'Ex',
        'y' => 'Wy', 'z' => 'Zed',
    ];

    protected $signature = 'learn:sync-assets
        {--audio-root= : Folder containing malLatterAtoZsound/ and latterWordAndSound/ (default: public/audio)}
        {--letters-out= : Destination for renamed letter-name clips (default: public/audio/letters/english)}
        {--words-out= : Destination for renamed word image/audio pairs (default: public/audio/words/english)}
        {--data-out= : Path to write the generated word-gallery data file (default: resources/js/features/learning/data/words.generated.ts)}';

    protected $description = "Move the raw English letter/word audio+image drop-ins into the app's asset convention and regenerate the word-gallery data file";

    public function handle(): int
    {
        $audioRoot = rtrim((string) ($this->option('audio-root') ?: public_path('audio')), '/\\');
        $lettersOut = rtrim((string) ($this->option('letters-out') ?: public_path('audio/letters/english')), '/\\');
        $wordsOut = rtrim((string) ($this->option('words-out') ?: public_path('audio/words/english')), '/\\');
        $dataOut = (string) ($this->option('data-out') ?: resource_path('js/features/learning/data/words.generated.ts'));

        $this->syncLetters("{$audioRoot}/malLatterAtoZsound", $lettersOut);
        $wordsByLetter = $this->syncWords("{$audioRoot}/latterWordAndSound", $wordsOut);
        $this->writeWordData($dataOut, $wordsByLetter);

        return self::SUCCESS;
    }

    private function syncLetters(string $source, string $destination): void
    {
        if (! File::isDirectory($source)) {
            $this->warn("No letter-sound folder at {$source}, skipping.");

            return;
        }

        File::ensureDirectoryExists($destination);
        $moved = 0;

        foreach (self::LETTER_NAMES as $char => $name) {
            $from = "{$source}/{$char}.mp3";

            if (! File::exists($from)) {
                $this->warn("Missing letter-sound file for \"{$char}\" ({$from}).");

                continue;
            }

            File::move($from, $destination.'/'.Str::slug($name).'.mp3');
            $moved++;
        }

        $this->info("Letters: moved {$moved}/".count(self::LETTER_NAMES).' name clips.');
    }

    /**
     * @return array<string, list<array{text: string, image: string, audio: string}>>
     */
    private function syncWords(string $source, string $destination): array
    {
        $imagesDir = "{$source}/audioImage";
        $soundsDir = "{$source}/audioSound";

        if (! File::isDirectory($imagesDir) || ! File::isDirectory($soundsDir)) {
            $this->warn("No word audioImage/audioSound folders under {$source}, skipping.");

            return [];
        }

        $images = $this->indexByBasename($imagesDir);
        $sounds = $this->indexByBasename($soundsDir);

        $complete = array_intersect_key($images, $sounds);
        $imageOnly = array_diff_key($images, $sounds);
        $soundOnly = array_diff_key($sounds, $images);

        File::ensureDirectoryExists($destination);

        $wordsByLetter = [];
        ksort($complete);

        foreach ($complete as $word => $image) {
            [$imagePath, $imageExt] = $image;
            [$soundPath, $soundExt] = $sounds[$word];

            $imageTarget = "{$destination}/{$word}.{$imageExt}";
            $soundTarget = "{$destination}/{$word}.{$soundExt}";
            File::move($imagePath, $imageTarget);
            File::move($soundPath, $soundTarget);

            $letter = mb_strtoupper(mb_substr($word, 0, 1));
            $wordsByLetter[$letter][] = [
                'text' => ucfirst($word),
                'image' => '/audio/words/english/'.basename($imageTarget),
                'audio' => '/audio/words/english/'.basename($soundTarget),
            ];
        }

        $this->info('Words: '.count($complete)." complete image+sound pairs moved into {$destination}.");

        if ($imageOnly !== []) {
            $this->warn('Skipped, image but no sound: '.implode(', ', array_keys($imageOnly)));
        }

        if ($soundOnly !== []) {
            $this->warn('Skipped, sound but no image: '.implode(', ', array_keys($soundOnly)));
        }

        return $wordsByLetter;
    }

    /**
     * Indexes a directory's files by lowercased basename (without extension),
     * preferring an .mp3 match when a word has more than one audio format.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private function indexByBasename(string $dir): array
    {
        $index = [];

        foreach (File::files($dir) as $file) {
            $basename = mb_strtolower($file->getFilenameWithoutExtension());
            $ext = mb_strtolower($file->getExtension());

            if (isset($index[$basename]) && $index[$basename][1] === 'mp3') {
                continue;
            }

            $index[$basename] = [$file->getPathname(), $ext];
        }

        return $index;
    }

    /**
     * @param  array<string, list<array{text: string, image: string, audio: string}>>  $wordsByLetter
     */
    private function writeWordData(string $path, array $wordsByLetter): void
    {
        ksort($wordsByLetter);

        $entries = [];

        foreach ($wordsByLetter as $letter => $words) {
            $items = collect($words)
                ->map(fn (array $w) => sprintf(
                    '        { text: %s, image: %s, audio: %s }',
                    json_encode($w['text'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    json_encode($w['image'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    json_encode($w['audio'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ))
                ->implode(",\n");

            $entries[] = "    {$letter}: [\n{$items},\n    ]";
        }

        $body = implode(",\n", $entries);

        $contents = <<<TS
        // AUTO-GENERATED by `php artisan learn:sync-assets` — do not edit by hand.
        // Re-run the command after adding or removing files under
        // public/audio/latterWordAndSound/{audioImage,audioSound}.
        export type LetterWord = { text: string; image: string; audio: string };

        export const WORDS_BY_LETTER: Record<string, LetterWord[]> = {
        {$body},
        };

        TS;

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);

        $this->info("Wrote word gallery data to {$path}.");
    }
}
