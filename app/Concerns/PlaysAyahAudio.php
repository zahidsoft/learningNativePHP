<?php

namespace App\Concerns;

use App\Services\Quran\AyahAudio;
use App\Services\Quran\DownloadAyahAudio;
use App\Services\Quran\QuranPreferences;
use Native\Mobile\Attributes\On;
use Nativephp\Speech\Events\AudioFinished;
use Nativephp\Speech\Facades\Speech;
use Throwable;

/**
 * Plays ayah recitations on a screen: from the phone when the file is
 * there, otherwise after downloading it once in the background. When
 * playing through, the next ayahs download ahead so there is no gap.
 */
trait PlaysAyahAudio
{
    /**
     * How many ayahs after the playing one are downloaded ahead.
     */
    private const AYAHS_FETCHED_AHEAD = 2;

    public ?int $playingAyah = null;

    public ?int $loadingAyah = null;

    public bool $isPaused = false;

    public bool $isPlayingThrough = false;

    public ?string $audioError = null;

    /**
     * Ayah numbers already sent to download from this screen.
     *
     * @var list<int>
     */
    public array $requestedDownloads = [];

    /**
     * Get the ayah numbers the screen can play, in reading order.
     *
     * @return list<int>
     */
    abstract protected function playableAyahs(): array;

    /**
     * Called when an ayah starts playing.
     */
    protected function ayahStarted(int $number): void
    {
        //
    }

    /**
     * Called when playing through reaches the last ayah.
     */
    protected function playedThroughToEnd(): void
    {
        //
    }

    /**
     * Play one ayah and stop after it.
     */
    public function playAyah(int $number): void
    {
        $this->isPlayingThrough = false;

        $this->startAyah($number);
    }

    /**
     * Play from the given ayah, or the first, to the end.
     */
    public function playThrough(?int $from = null): void
    {
        $from ??= $this->playableAyahs()[0] ?? null;

        if ($from === null) {
            return;
        }

        $this->isPlayingThrough = true;

        $this->startAyah($from);
    }

    public function togglePause(): void
    {
        if ($this->playingAyah === null) {
            $this->playThrough();

            return;
        }

        if ($this->isPaused) {
            $this->isPaused = ! Speech::resume();
        } else {
            $this->isPaused = Speech::pause();
        }
    }

    public function stopAudio(): void
    {
        Speech::stop();

        $this->playingAyah = null;
        $this->loadingAyah = null;
        $this->isPaused = false;
        $this->isPlayingThrough = false;
    }

    #[On(AudioFinished::class)]
    public function audioFinished(string $path = ''): void
    {
        $finished = $this->playingAyah;

        if ($finished === null || ! $this->isPlayingThrough) {
            $this->playingAyah = null;

            return;
        }

        $next = $this->ayahAfter($finished);

        if ($next === null) {
            $this->stopAudio();
            $this->playedThroughToEnd();

            return;
        }

        $this->startAyah($next);
    }

    public function unmount(): void
    {
        if ($this->playingAyah !== null) {
            Speech::stop();
        }

        parent::unmount();
    }

    private function startAyah(int $number): void
    {
        $audio = app(AyahAudio::class);
        $reciter = app(QuranPreferences::class)->reciter();

        $this->audioError = null;
        $this->isPaused = false;

        if ($audio->isDownloaded($reciter, $number)) {
            $this->startPlayback($number, $audio->path($reciter, $number));

            return;
        }

        if ($this->loadingAyah === $number) {
            return;
        }

        Speech::stop();
        $this->playingAyah = null;
        $this->loadingAyah = $number;
        $this->requestedDownloads[] = $number;

        DownloadAyahAudio::dispatch($reciter, $number)
            ->finished(fn (string $path) => $this->loadingAyah === $number ? $this->startPlayback($number, $path) : null)
            ->failed(function (Throwable $exception) use ($number): void {
                if ($this->loadingAyah === $number) {
                    $this->loadingAyah = null;
                    $this->isPlayingThrough = false;
                    $this->audioError = $exception->getMessage();
                }
            });
    }

    private function startPlayback(int $number, string $path): void
    {
        $this->loadingAyah = null;
        $this->playingAyah = Speech::playFile($path) ? $number : null;

        if ($this->playingAyah === null) {
            $this->isPlayingThrough = false;
            $this->audioError = 'তিলাওয়াত চালু করা যায়নি।';

            return;
        }

        $this->ayahStarted($number);

        if ($this->isPlayingThrough) {
            $this->fetchAhead($number);
        }
    }

    /**
     * Download the next few ayahs in the background while this one plays.
     */
    private function fetchAhead(int $number): void
    {
        $audio = app(AyahAudio::class);
        $reciter = app(QuranPreferences::class)->reciter();
        $next = $number;

        for ($step = 0; $step < self::AYAHS_FETCHED_AHEAD; $step++) {
            $next = $this->ayahAfter($next);

            if ($next === null) {
                return;
            }

            if ($audio->isDownloaded($reciter, $next) || in_array($next, $this->requestedDownloads, true)) {
                continue;
            }

            $this->requestedDownloads[] = $next;

            DownloadAyahAudio::dispatch($reciter, $next);
        }
    }

    private function ayahAfter(int $number): ?int
    {
        $ayahs = $this->playableAyahs();
        $position = array_search($number, $ayahs, true);

        return $position === false ? null : ($ayahs[$position + 1] ?? null);
    }
}
