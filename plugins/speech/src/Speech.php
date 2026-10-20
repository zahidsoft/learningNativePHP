<?php

namespace Nativephp\Speech;

class Speech
{
    /**
     * Speak text aloud using the device's native text-to-speech engine.
     */
    public function speak(string $text, ?string $lang = null, ?float $rate = null): ?object
    {
        return $this->call('Speech.Speak', array_filter([
            'text' => $text,
            'lang' => $lang,
            'rate' => $rate,
        ], fn ($v) => $v !== null));
    }

    /**
     * Play a recorded audio clip from an absolute path on the device.
     *
     * Returns false when the clip could not be started (no native bridge, or
     * the file is missing or unplayable), so callers can fall back to speak().
     */
    public function playFile(string $path): bool
    {
        return (bool) ($this->call('Speech.PlayFile', ['path' => $path])->playing ?? false);
    }

    /**
     * Pause the clip currently playing, keeping its position.
     *
     * Returns false when nothing was playing.
     */
    public function pause(): bool
    {
        return (bool) ($this->call('Speech.Pause')->paused ?? false);
    }

    /**
     * Carry on playing a paused clip from where it stopped.
     *
     * Returns false when there was no paused clip.
     */
    public function resume(): bool
    {
        return (bool) ($this->call('Speech.Resume')->playing ?? false);
    }

    /**
     * Stop any speech or audio clip currently playing.
     */
    public function stop(): ?object
    {
        return $this->call('Speech.Stop');
    }

    /**
     * Call a bridge function and return the data it answered with.
     *
     * On the device the bridge answers with the function's data as is, such
     * as {"playing":true}; some bridges wrap it as {"data":{...}}, so both
     * shapes are read. Returns null when there is no native bridge.
     *
     * @param  array<string, mixed>  $parameters
     */
    private function call(string $method, array $parameters = []): ?object
    {
        if (! function_exists('nativephp_call')) {
            return null;
        }

        $result = nativephp_call($method, json_encode((object) $parameters));

        if (! $result) {
            return null;
        }

        $decoded = json_decode($result);

        if (! is_object($decoded)) {
            return null;
        }

        return is_object($decoded->data ?? null) ? $decoded->data : $decoded;
    }
}
