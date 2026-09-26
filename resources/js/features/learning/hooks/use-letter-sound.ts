import { letterAudioSrc, phraseAudioSrc, speak } from "@/lib/speech";
import type { CatId } from "@/features/learning/types";

/**
 * Speaks letters/words for the learning screens. Tries a real recorded
 * voice clip first (public/audio/letters/<catId>/<slug>.mp3) and only
 * falls back to text-to-speech when that file hasn't been added yet, so
 * the app sounds natural as soon as recordings are dropped in.
 */
export function useLetterSound() {
    const speakLetter = (catId: CatId, name: string, lang: string) =>
        speak(name, { audioSrc: letterAudioSrc(catId, name), lang });

    const speakQuizFeedback = (correct: boolean) =>
        speak(correct ? "Correct" : "Try again", {
            audioSrc: phraseAudioSrc(correct ? "correct" : "wrong"),
            lang: "en-US",
        });

    return { speakLetter, speakQuizFeedback };
}
