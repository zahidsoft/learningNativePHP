<?php

namespace Nativephp\Speech\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A clip started with Speech::playFile() played to its end. It is not sent
 * when the clip is stopped or replaced by another one.
 */
class AudioFinished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $path,
    ) {}
}
