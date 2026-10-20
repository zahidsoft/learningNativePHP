<?php

namespace App\Actions\Quran;

use App\Models\Khatam;
use App\Models\KhatamAyah;
use App\Services\Quran\Mushaf;

class RecordAyahsRead
{
    /**
     * Count ayahs as read in the khatam in progress. An ayah already counted
     * is ignored, and the khatam completes once every ayah is read.
     *
     * @param  list<array{number: int, juz: int}>  $ayahs
     */
    public function handle(array $ayahs): Khatam
    {
        $khatam = Khatam::current();
        $now = now();

        KhatamAyah::query()->insertOrIgnore(array_map(fn (array $ayah): array => [
            'khatam_id' => $khatam->id,
            'ayah_number' => $ayah['number'],
            'juz' => $ayah['juz'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $ayahs));

        if ($khatam->ayahs()->count() >= Mushaf::AYAH_COUNT) {
            $khatam->completed_at = $now;
            $khatam->save();
        }

        return $khatam;
    }
}
