/**
 * Speech Plugin for NativePHP Mobile
 *
 * Native text-to-speech (Android's TextToSpeech engine / iOS's
 * AVSpeechSynthesizer) via the NativePHP bridge. Android's System WebView
 * doesn't implement `window.speechSynthesis`, so this plugin replaces it.
 *
 * @example
 * import { speech } from '@nativephp/plugin-speech';
 *
 * await speech.speak('Apple', { lang: 'en-US' });
 * await speech.stop();
 */

const baseUrl = '/_native/api/call';

/**
 * Internal bridge call function
 * @private
 */
async function bridgeCall(method, params = {}) {
    const response = await fetch(baseUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ method, params })
    });

    const result = await response.json();

    if (result.status === 'error') {
        throw new Error(result.message || 'Native call failed');
    }

    const nativeResponse = result.data;
    if (nativeResponse && nativeResponse.data !== undefined) {
        return nativeResponse.data;
    }

    return nativeResponse;
}

/**
 * Speak text aloud using the device's native text-to-speech engine.
 * @param {string} text
 * @param {{ lang?: string, rate?: number }} [options]
 * @returns {Promise<{ speaking: boolean }>}
 */
export async function speak(text, options = {}) {
    return bridgeCall('Speech.Speak', { text, ...options });
}

/**
 * Stop any speech currently playing.
 * @returns {Promise<{ stopped: boolean }>}
 */
export async function stop() {
    return bridgeCall('Speech.Stop');
}

/**
 * Speech namespace object
 */
export const speech = {
    speak,
    stop,
};

export default speech;
