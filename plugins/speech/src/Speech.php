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
     * Stop any speech currently playing.
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
