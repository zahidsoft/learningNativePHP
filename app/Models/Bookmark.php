<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * An ayah the reader saved to come back to.
 *
 * @property int $id
 * @property int $ayah_number Position of the ayah in the whole Quran, 1 to 6236.
 * @property int $surah
 * @property int $ayah_in_surah
 * @property string $surah_name
 * @property string|null $note
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['ayah_number', 'surah', 'ayah_in_surah', 'surah_name', 'note'])]
class Bookmark extends Model
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
            'surah' => 'integer',
            'ayah_in_surah' => 'integer',
        ];
    }
}
