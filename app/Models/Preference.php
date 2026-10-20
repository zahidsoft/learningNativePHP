<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * One saved setting of the app owner, such as the chosen translation.
 *
 * @property int $id
 * @property string $key
 * @property mixed $value
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['key', 'value'])]
class Preference extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    /**
     * Read a setting, falling back to the default when it was never saved.
     */
    public static function read(string $key, mixed $default = null): mixed
    {
        $preference = self::query()->where('key', $key)->first();

        return $preference === null ? $default : $preference->value;
    }

    /**
     * Save a setting, replacing any earlier value.
     */
    public static function write(string $key, mixed $value): void
    {
        self::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
