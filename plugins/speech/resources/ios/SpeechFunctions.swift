import Foundation
import AVFoundation

/// Speech.* — native text-to-speech, backed by `AVSpeechSynthesizer`.
enum SpeechFunctions {

    /// One synthesizer per process, reused across calls.
    private static let synthesizer = AVSpeechSynthesizer()

    /// Parameters:
    ///   - text: string (required) — what to say
    ///   - lang: string (optional, BCP-47 tag e.g. "en-US", "ar-SA", "bn-BD") — default "en-US"
    ///   - rate: number (optional, 0.1–2.0, mapped onto AVSpeechUtterance's 0...1 rate) — default 0.85
    /// Returns:
    ///   - speaking: boolean
    class Speak: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let text = (parameters["text"] as? String)?.trimmingCharacters(in: .whitespacesAndNewlines) ?? ""
            guard !text.isEmpty else {
                return BridgeResponse.error(code: "EMPTY_TEXT", message: "No text provided to speak")
            }

            let langTag = (parameters["lang"] as? String) ?? "en-US"
            let rate: Float
            if let r = parameters["rate"] as? NSNumber {
                rate = r.floatValue
            } else {
                rate = 0.85
            }

            let utterance = AVSpeechUtterance(string: text)
            utterance.voice = AVSpeechSynthesisVoice(language: langTag) ?? AVSpeechSynthesisVoice(language: "en-US")
            // AVSpeechUtterance rate is 0...1 (AVSpeechUtteranceDefaultSpeechRate ~= 0.5);
            // our JS-side "rate" mirrors the Web Speech API's 0.1...2 scale, so remap it.
            let normalizedRate = AVSpeechUtteranceDefaultSpeechRate * (rate / 1.0)
            utterance.rate = min(max(normalizedRate, AVSpeechUtteranceMinimumSpeechRate), AVSpeechUtteranceMaximumSpeechRate)

            // A new "Tap to hear" / answer tap should interrupt whatever is
            // currently being read, not queue behind it.
            if SpeechFunctions.synthesizer.isSpeaking {
                SpeechFunctions.synthesizer.stopSpeaking(at: .immediate)
            }
            SpeechFunctions.synthesizer.speak(utterance)

            return BridgeResponse.success(data: ["speaking": true])
        }
    }

    /// Stop any speech currently playing.
    class Stop: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let stopped = SpeechFunctions.synthesizer.stopSpeaking(at: .immediate)
            return BridgeResponse.success(data: ["stopped" : stopped])
        }
    }
}
