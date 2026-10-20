<?php

namespace App\Actions\Quran;

use App\Models\Khatam;
use App\Services\Quran\AlQuranClient;
use App\Services\Quran\Mushaf;

/**
 * @phpstan-type JuzProgress array{juz: int, read: int, total: int, startPage: int}
 */
class SummarizeKhatam
{
    public function __construct(private AlQuranClient $client)
    {
        //
    }

    /**
     * Gather how far the latest khatam has got, juz by juz.
     *
     * @return array{khatam: Khatam, read: int, total: int, percent: int, completedCount: int, juzs: list<JuzProgress>}
     */
    public function handle(): array
    {
        $khatam = Khatam::latestOrStart();
        $readByJuz = $khatam->ayahs()
            ->selectRaw('juz, count(*) as total')
            ->groupBy('juz')
            ->pluck('total', 'juz');

        $juzs = [];

        foreach ($this->ayahsPerJuz() as $juz => $total) {
            $juzs[] = [
                'juz' => $juz,
                'read' => (int) ($readByJuz[$juz] ?? 0),
                'total' => $total,
                'startPage' => Mushaf::juzStartPage($juz),
            ];
        }

        $read = (int) $readByJuz->sum();

        return [
            'khatam' => $khatam,
            'read' => $read,
            'total' => Mushaf::AYAH_COUNT,
            'percent' => (int) floor($read / Mushaf::AYAH_COUNT * 100),
            'completedCount' => Khatam::query()->whereNotNull('completed_at')->count(),
            'juzs' => $juzs,
        ];
    }

    /**
     * Count the ayahs in each juz from where each juz starts, as listed in
     * the API's metadata.
     *
     * @return array<int, int>
     */
    private function ayahsPerJuz(): array
    {
        $meta = $this->client->remember('meta');

        $surahStarts = [];
        $ayahsBefore = 0;

        foreach ($meta['surahs']['references'] as $surah) {
            $surahStarts[(int) $surah['number']] = $ayahsBefore;
            $ayahsBefore += (int) $surah['numberOfAyahs'];
        }

        $juzStarts = array_map(
            fn (array $start): int => $surahStarts[(int) $start['surah']] + (int) $start['ayah'],
            array_values($meta['juzs']['references']),
        );

        $counts = [];

        foreach ($juzStarts as $index => $start) {
            $nextStart = $juzStarts[$index + 1] ?? Mushaf::AYAH_COUNT + 1;
            $counts[$index + 1] = $nextStart - $start;
        }

        return $counts;
    }
}
