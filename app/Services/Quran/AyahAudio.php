<?php

namespace App\Services\Quran;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Recitations of single ayahs, downloaded the first time they are played and
 * kept on the phone so each file is only ever downloaded once.
 */
class AyahAudio
{
    /**
     * Not every reciter is published at every bitrate, so a missing file is
     * tried again at the next one.
     *
     * @var list<int>
     */
    private const FALLBACK_BITRATES = [64, 128, 48, 192, 40, 32];

    /**
     * A lock older than this was left by a download that died with the app.
     */
    private const STALE_LOCK_SECONDS = 120;

    /**
     * How long to wait for another download of the same file to finish.
     */
    private const WAIT_SECONDS = 60;

    /**
     * Get where the recitation of an ayah is kept on the phone.
     */
    public function path(string $reciter, int $ayah): string
    {
        return $this->directory($reciter)."/{$ayah}.mp3";
    }

    public function isDownloaded(string $reciter, int $ayah): bool
    {
        return is_file($this->path($reciter, $ayah));
    }

    /**
     * Make sure the recitation of an ayah is on the phone and return its path.
     *
     * The file is written under a temporary name and renamed once complete,
     * so a download cut off half way never passes for a finished file. A lock
     * file stops two downloads of the same ayah running side by side.
     *
     * @throws QuranUnavailableException
     */
    public function download(string $reciter, int $ayah): string
    {
        $path = $this->path($reciter, $ayah);

        if (is_file($path)) {
            return $path;
        }

        File::ensureDirectoryExists(dirname($path));

        $lock = "{$path}.lock";

        if (! $this->acquireLock($lock)) {
            return $this->waitForOtherDownload($path, $lock);
        }

        $partial = "{$path}.part";

        try {
            foreach ($this->bitrates() as $bitrate) {
                $response = Http::timeout(config('services.alquran.timeout'))
                    ->sink($partial)
                    ->get(config('services.alquran.audio_url')."/{$bitrate}/{$reciter}/{$ayah}.mp3");

                if ($response->successful() && filesize($partial) > 0) {
                    rename($partial, $path);

                    return $path;
                }
            }
        } catch (ConnectionException) {
            throw QuranUnavailableException::offline();
        } finally {
            File::delete([$partial, $lock]);
        }

        throw new QuranUnavailableException('এই ক্বারীর তিলাওয়াত এই আয়াতের জন্য পাওয়া যায়নি।');
    }

    /**
     * Get how many bytes of recitations of each reciter are on the phone.
     *
     * @return array<string, int>
     */
    public function downloadedSizes(): array
    {
        $sizes = [];

        foreach (File::directories($this->root()) as $directory) {
            $bytes = array_sum(array_map(
                fn (string $file): int => (int) filesize($file),
                File::glob("{$directory}/*.mp3"),
            ));

            if ($bytes > 0) {
                $sizes[basename($directory)] = $bytes;
            }
        }

        return $sizes;
    }

    /**
     * Remove every downloaded recitation of a reciter.
     */
    public function deleteReciter(string $reciter): void
    {
        File::deleteDirectory($this->directory($reciter));
    }

    private function root(): string
    {
        $root = storage_path('app/quran-audio');

        File::ensureDirectoryExists($root);

        return $root;
    }

    private function directory(string $reciter): string
    {
        return $this->root().'/'.basename($reciter);
    }

    /**
     * @return list<int>
     */
    private function bitrates(): array
    {
        return array_values(array_unique([(int) config('services.alquran.audio_bitrate'), ...self::FALLBACK_BITRATES]));
    }

    private function acquireLock(string $lock): bool
    {
        if (is_file($lock) && time() - (int) filemtime($lock) > self::STALE_LOCK_SECONDS) {
            File::delete($lock);
        }

        $handle = @fopen($lock, 'x');

        if ($handle === false) {
            return false;
        }

        fclose($handle);

        return true;
    }

    /**
     * Wait for the download that holds the lock and use its file.
     *
     * @throws QuranUnavailableException
     */
    private function waitForOtherDownload(string $path, string $lock): string
    {
        $deadline = time() + self::WAIT_SECONDS;

        while (is_file($lock) && ! is_file($path) && time() < $deadline) {
            usleep(200_000);
        }

        return is_file($path) ? $path : throw QuranUnavailableException::offline();
    }
}
