<?php

namespace App\Actions\Quran;

use App\Models\Bookmark;

class ToggleBookmark
{
    /**
     * Save an ayah, or remove it when it is already saved.
     *
     * @return bool Whether the ayah is saved afterwards.
     */
    public function handle(int $ayahNumber, int $surah, int $ayahInSurah, string $surahName): bool
    {
        $deleted = Bookmark::query()->where('ayah_number', $ayahNumber)->delete();

        if ($deleted > 0) {
            return false;
        }

        Bookmark::query()->create([
            'ayah_number' => $ayahNumber,
            'surah' => $surah,
            'ayah_in_surah' => $ayahInSurah,
            'surah_name' => $surahName,
        ]);

        return true;
    }
}
