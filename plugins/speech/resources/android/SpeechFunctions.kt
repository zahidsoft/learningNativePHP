package com.nativephp.plugins.speech

import android.content.Context
import android.speech.tts.TextToSpeech
import android.speech.tts.UtteranceProgressListener
import com.nativephp.mobile.bridge.BridgeFunction
import com.nativephp.mobile.bridge.BridgeResponse
import java.util.Locale
import java.util.concurrent.CountDownLatch
import java.util.concurrent.TimeUnit
import java.util.concurrent.atomic.AtomicBoolean

/**
 * Speech.* — native text-to-speech, backed by Android's built-in
 * `android.speech.tts.TextToSpeech` engine.
 *
 * Android's System WebView does not implement the Web Speech API
 * (`window.speechSynthesis` is undefined there even though it works in a
 * desktop browser), so "Tap to hear" needs a real native bridge instead of
 * calling into JS speech synthesis.
 */
object SpeechFunctions {

    // One TTS engine per process, lazily created on first use and reused
    // across calls so repeated taps don't reinitialize it.
    @Volatile
    private var engine: TextToSpeech? = null

    @Volatile
    private var engineReady = false

    private val initLock = Object()

    /**
     * Block (briefly) until the shared TextToSpeech engine is initialized.
     * The engine boots asynchronously the first time it's used; subsequent
     * calls return immediately since `engineReady` is already true.
     */
    private fun ensureEngine(context: Context): TextToSpeech? {
        val existing = engine
        if (existing != null && engineReady) {
            return existing
        }

        synchronized(initLock) {
            val current = engine
            if (current != null && engineReady) {
                return current
            }

            val latch = CountDownLatch(1)
            val created = TextToSpeech(context.applicationContext) { status ->
                engineReady = status == TextToSpeech.SUCCESS
                latch.countDown()
            }
            engine = created

            // Engine init is a one-off async callback from the OS TTS
            // service; wait briefly so the very first Speak call doesn't
            // race it and silently drop the utterance.
            latch.await(4, TimeUnit.SECONDS)

            return if (engineReady) created else null
        }
    }

    /**
     * Parameters:
     *   - text: string (required) — what to say
     *   - lang: string (optional, BCP-47 tag e.g. "en-US", "ar-SA", "bn-BD") — default "en-US"
     *   - rate: number (optional, 0.1–2.0) — default 0.85
     * Returns:
     *   - speaking: boolean
     */
    class Speak(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val text = (parameters["text"] as? String)?.trim().orEmpty()
            if (text.isEmpty()) {
                return BridgeResponse.error("EMPTY_TEXT", "No text provided to speak")
            }

            val tts = ensureEngine(context)
                ?: return BridgeResponse.error("ENGINE_UNAVAILABLE", "Text-to-speech engine failed to initialize")

            val langTag = (parameters["lang"] as? String) ?: "en-US"
            val rate = when (val r = parameters["rate"]) {
                is Number -> r.toFloat()
                else -> 0.85f
            }

            val locale = Locale.forLanguageTag(langTag)
            val supported = tts.isLanguageAvailable(locale)
            tts.language = if (supported >= TextToSpeech.LANG_AVAILABLE) locale else Locale.US
            tts.setSpeechRate(rate)

            val spoke = AtomicBoolean(false)
            val utteranceId = "nativephp-speech-${System.currentTimeMillis()}"
            tts.setOnUtteranceProgressListener(object : UtteranceProgressListener() {
                override fun onStart(id: String?) {
                    spoke.set(true)
                }

                override fun onDone(id: String?) {}

                @Deprecated("Deprecated in Java")
                override fun onError(id: String?) {}
            })

            // QUEUE_FLUSH: a new "Tap to hear" / answer tap should interrupt
            // whatever is currently being read, not queue behind it.
            val result = tts.speak(text, TextToSpeech.QUEUE_FLUSH, null, utteranceId)

            return BridgeResponse.success(mapOf(
                "speaking" to (result == TextToSpeech.SUCCESS)
            ))
        }
    }

    /**
     * Stop any speech currently playing.
     */
    class Stop(private val context: Context) : BridgeFunction {
        override fun execute(parameters: Map<String, Any>): Map<String, Any> {
            val tts = engine
            val stopped = if (tts != null && engineReady) {
                tts.stop() == TextToSpeech.SUCCESS
            } else {
                true
            }

            return BridgeResponse.success(mapOf("stopped" to stopped))
        }
    }
}
