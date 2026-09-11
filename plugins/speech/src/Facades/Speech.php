<?php

namespace Nativephp\Speech\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static object|null speak(string $text, ?string $lang = null, ?float $rate = null)
 * @method static object|null stop()
 *
 * @see \Nativephp\Speech\Speech
 */
class Speech extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Nativephp\Speech\Speech::class;
    }
}