<?php

namespace Nativephp\Speech;

class Speech
{
    /**
     * Speak text aloud using the device's native text-to-speech engine.
     */
    public function speak(string $text, ?string $lang = null, ?float $rate = null): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('Speech.Speak', json_encode(array_filter([
                'text' => $text,
                'lang' => $lang,
                'rate' => $rate,
            ], fn ($v) => $v !== null)));

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }

    /**
     * Play a recorded audio clip from an absolute path on the device.
     *
     * Returns false when the clip could not be started (no native bridge, or
     * the file is missing or unplayable), so callers can fall back to speak().
     */
    public function playFile(string $path): bool
    {
        if (! function_exists('nativephp_call')) {
            return false;
        }

        $result = nativephp_call('Speech.PlayFile', json_encode(['path' => $path]));

        if (! $result) {
            return false;
        }

        return (bool) (json_decode($result)->data->playing ?? false);
    }

    /**
     * Call a parameterless bridge function and read one boolean from its data.
     */
    private function callForFlag(string $method, string $flag): bool
    {
        if (! function_exists('nativephp_call')) {
            return false;
        }

        $result = nativephp_call($method, '{}');

        if (! $result) {
            return false;
        }

        return (bool) (json_decode($result)->data->{$flag} ?? false);
    }

    /**
     * Pause the clip currently playing, keeping its position.
     *
     * Returns false when nothing was playing.
     */
    public function pause(): bool
    {
        return $this->callForFlag('Speech.Pause', 'paused');
    }

    /**
     * Carry on playing a paused clip from where it stopped.
     *
     * Returns false when there was no paused clip.
     */
    public function resume(): bool
    {
        return $this->callForFlag('Speech.Resume', 'playing');
    }

    /**
     * Stop any speech or audio clip currently playing.
     */
    public function stop(): ?object
    {
        if (function_exists('nativephp_call')) {
            $result = nativephp_call('Speech.Stop', '{}');

            if ($result) {
                $decoded = json_decode($result);

                return $decoded->data ?? null;
            }
        }

        return null;
    }
}
