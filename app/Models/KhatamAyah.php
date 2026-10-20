<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An ayah counted as read in a khatam. Each ayah counts once per khatam.
 *
 * @property int $id
 * @property int $khatam_id
 * @property int $ayah_number
 * @property int $juz
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Khatam $khatam
 */
#[Fillable(['khatam_id', 'ayah_number', 'juz'])]
class KhatamAyah extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ayah_number' => 'integer',
            'juz' => 'integer',
        ];
    }

    /**
     * Get the khatam the ayah was read in.
     *
     * @return BelongsTo<Khatam, $this>
     */
    public function khatam(): BelongsTo
    {
        return $this->belongsTo(Khatam::class);
    }
}
