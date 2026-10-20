<?php

namespace App\Services\Quran;

use Native\Mobile\AsyncTask;

/**
 * Downloads one ayah's recitation on a background thread, so the screen
 * stays responsive while the file arrives.
 */
class DownloadAyahAudio extends AsyncTask
{
    /**
     * @return string The path of the recitation on the phone.
     */
    public function handle(string $reciter, int $ayah): string
    {
        return app(AyahAudio::class)->download($reciter, $ayah);
    }
}
