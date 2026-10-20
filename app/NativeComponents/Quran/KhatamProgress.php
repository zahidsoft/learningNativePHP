<?php

namespace App\NativeComponents\Quran;

use App\Actions\Quran\SummarizeKhatam;
use App\Models\Khatam;
use App\Services\Quran\QuranUnavailableException;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

#[Lazy]
class KhatamProgress extends NativeComponent
{
    public function render(): Element
    {
        $summary = null;
        $error = null;

        try {
            $summary = app(SummarizeKhatam::class)->handle();
        } catch (QuranUnavailableException $exception) {
            $error = $exception->getMessage();
        }

        return $this->view('quran.khatam-progress', [
            'summary' => $summary,
            'error' => $error,
        ]);
    }

    /**
     * Begin a new khatam once the last one is finished.
     */
    public function startNew(): void
    {
        if (Khatam::latestOrStart()->isComplete()) {
            Khatam::query()->create();
        }
    }

    /**
     * Re-render, loading the juz sizes again after they failed to load.
     */
    public function retry(): void
    {
        //
    }
}
