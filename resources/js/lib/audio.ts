// Thin wrapper around HTMLAudioElement for playing short local sound files
// (voice lines, SFX). Kept generic and feature-agnostic so any part of the
// app — learning, chat message tones, incoming-call rings, etc. — can play
// a sound the same way.
const cache = new Map<string, HTMLAudioElement>();

function loadAudio(src: string): HTMLAudioElement {
    let audio = cache.get(src);
    if (!audio) {
        audio = new Audio(src);
        audio.preload = "auto";
        cache.set(src, audio);
    }
    return audio;
}

/**
 * Plays an audio file. Resolves once playback finishes; rejects if the file
 * is missing, fails to decode, or can't play (e.g. autoplay restrictions) —
 * callers use that to fall back to another sound source.
 */
export function playAudioFile(src: string): Promise<void> {
    if (typeof window === "undefined" || typeof Audio === "undefined") {
        return Promise.reject(new Error("Audio is not available"));
    }

    const audio = loadAudio(src);

    return new Promise((resolve, reject) => {
        const cleanup = (fn: () => void) => {
            audio.removeEventListener("ended", onEnded);
            audio.removeEventListener("error", onError);
            fn();
        };
        const onEnded = () => cleanup(resolve);
        const onError = () =>
            cleanup(() =>
                reject(new Error(`Missing or unplayable audio file: ${src}`)),
            );

        audio.addEventListener("ended", onEnded);
        audio.addEventListener("error", onError);
        audio.currentTime = 0;
        audio.play().catch((e) => cleanup(() => reject(e)));
    });
}
