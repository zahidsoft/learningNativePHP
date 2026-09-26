import { playAudioFile } from "@/lib/audio";
import { nativeBridgeCall } from "@/lib/native-bridge";

/**
 * Turns a label into a filename-safe slug, e.g. "Double-u" -> "double-u",
 * "borgio jo" -> "borgio-jo". Used to derive the expected audio file path
 * for a given letter/word straight from its existing display name, so
 * dropping in a real recording needs no code change.
 */
export function slugify(name: string): string {
    return name
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
}

/**
 * Path a recorded voice clip for a letter/word should live at. Category
 * folders keep e.g. English "one" and Numbers "one" from colliding.
 * Drop a matching .mp3 into public/audio/letters/<group>/ to upgrade that
 * entry from text-to-speech to a real recorded voice — no code change.
 */
export function letterAudioSrc(group: string, name: string): string {
    return `/audio/letters/${group}/${slugify(name)}.mp3`;
}

/** Path a short UI voice line (e.g. quiz feedback) should live at. */
export function phraseAudioSrc(key: string): string {
    return `/audio/sfx/${key}.mp3`;
}

/**
 * Speaks `text` for the user. Prefers a real recorded voice clip at
 * `audioSrc` when one has been added; falls back to the platform's
 * text-to-speech when it hasn't (native Speech.Speak bridge, then the Web
 * Speech API for desktop browser previews) — so the app sounds natural as
 * soon as audio files are supplied, and never goes silent before that.
 */
export async function speak(
    text: string,
    opts: { audioSrc: string; lang?: string },
): Promise<void> {
    try {
        await playAudioFile(opts.audioSrc);
        return;
    } catch {
        // No recording at this path yet — fall through to TTS.
    }

    const lang = opts.lang || "en-US";
    try {
        await nativeBridgeCall("Speech.Speak", { text, lang, rate: 0.85 });
        return;
    } catch {
        // Not running inside the native shell (e.g. `npm run dev` in a
        // desktop browser) — fall back to the Web Speech API.
    }

    if (typeof window === "undefined" || !window.speechSynthesis) return;
    try {
        window.speechSynthesis.cancel();
        const u = new SpeechSynthesisUtterance(text);
        u.lang = lang;
        u.rate = 0.8;
        window.speechSynthesis.speak(u);
    } catch {
        /* speech not available in this webview */
    }
}
