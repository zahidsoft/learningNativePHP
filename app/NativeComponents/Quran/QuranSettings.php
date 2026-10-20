<?php

namespace App\NativeComponents\Quran;

use App\Actions\Quran\ListReciters;
use App\Enums\Translation;
use App\Services\Quran\AyahAudio;
use App\Services\Quran\DownloadAyahAudio;
use App\Services\Quran\QuranPreferences;
use App\Services\Quran\QuranUnavailableException;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;
use Nativephp\Speech\Facades\Speech;
use Throwable;

/**
 * @phpstan-import-type Reciter from ListReciters
 */
#[Lazy]
class QuranSettings extends NativeComponent
{
    /**
     * The ayah each reciter is previewed with: Al-Fatiha's first.
     */
    public const PREVIEW_AYAH = 1;

    /**
     * @var list<Reciter>
     */
    public array $reciters = [];

    public ?string $previewing = null;

    public ?string $error = null;

    public function mount(): void
    {
        $this->loadReciters();
    }

    public function render(): Element
    {
        $preferences = app(QuranPreferences::class);

        return $this->view('quran.quran-settings', [
            'translations' => Translation::cases(),
            'translation' => $preferences->translation(),
            'reciter' => $preferences->reciter(),
            'downloads' => app(AyahAudio::class)->downloadedSizes(),
            'reciterNames' => array_column($this->reciters, 'englishName', 'identifier'),
        ]);
    }

    public function loadReciters(): void
    {
        try {
            $this->reciters = app(ListReciters::class)->handle();
            $this->error = null;
        } catch (QuranUnavailableException $exception) {
            $this->error = $exception->getMessage();
        }
    }

    public function chooseTranslation(string $translation): void
    {
        $chosen = Translation::tryFrom($translation);

        if ($chosen !== null) {
            app(QuranPreferences::class)->setTranslation($chosen);
        }
    }

    public function chooseReciter(string $identifier): void
    {
        if (in_array($identifier, array_column($this->reciters, 'identifier'), true)) {
            app(QuranPreferences::class)->setReciter($identifier);
        }
    }

    /**
     * Let the reader hear a reciter before choosing them.
     */
    public function preview(string $identifier): void
    {
        $audio = app(AyahAudio::class);

        Speech::stop();
        $this->previewing = $identifier;

        if ($audio->isDownloaded($identifier, self::PREVIEW_AYAH)) {
            Speech::playFile($audio->path($identifier, self::PREVIEW_AYAH));
            $this->previewing = null;

            return;
        }

        DownloadAyahAudio::dispatch($identifier, self::PREVIEW_AYAH)
            ->finished(function (string $path) use ($identifier): void {
                if ($this->previewing === $identifier) {
                    Speech::playFile($path);
                    $this->previewing = null;
                }
            })
            ->failed(function (Throwable $exception): void {
                $this->previewing = null;
                $this->error = $exception->getMessage();
            });
    }

    /**
     * Free the space taken by a reciter's downloaded recitations.
     */
    public function deleteDownloads(string $identifier): void
    {
        app(AyahAudio::class)->deleteReciter($identifier);
    }

    public function unmount(): void
    {
        Speech::stop();

        parent::unmount();
    }
}
