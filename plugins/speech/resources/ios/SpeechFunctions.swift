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

            SpeechFunctions.player?.stop()

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

    /// One clip at a time: a new tap replaces whatever clip is still playing.
    private static var player: AVAudioPlayer?

    /// Tells PHP when a clip plays to its end (not when it is stopped or replaced).
    private static let finishAnnouncer = FinishAnnouncer()

    private final class FinishAnnouncer: NSObject, AVAudioPlayerDelegate {
        func audioPlayerDidFinishPlaying(_ player: AVAudioPlayer, successfully flag: Bool) {
            guard flag, let path = player.url?.path else {
                return
            }

            DispatchQueue.main.async {
                LaravelBridge.shared.send?("Nativephp\\Speech\\Events\\AudioFinished", ["path": path])
            }
        }
    }

    /// Play a recorded audio clip (voice line, SFX) from the device filesystem.
    ///
    /// When the clip plays to its end, an AudioFinished event carrying its
    /// path is sent to PHP, so a screen can move on to the next clip.
    ///
    /// Parameters:
    ///   - path: string (required) — absolute path of the audio file
    /// Returns:
    ///   - playing: boolean
    class PlayFile: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            let path = (parameters["path"] as? String)?.trimmingCharacters(in: .whitespacesAndNewlines) ?? ""
            guard !path.isEmpty else {
                return BridgeResponse.error(code: "EMPTY_PATH", message: "No audio file path provided")
            }
            guard FileManager.default.fileExists(atPath: path) else {
                return BridgeResponse.error(code: "FILE_NOT_FOUND", message: "No audio file at \(path)")
            }

            // A clip and the TTS voice must never talk over each other.
            if SpeechFunctions.synthesizer.isSpeaking {
                SpeechFunctions.synthesizer.stopSpeaking(at: .immediate)
            }
            SpeechFunctions.player?.stop()

            do {
                let created = try AVAudioPlayer(contentsOf: URL(fileURLWithPath: path))
                created.delegate = SpeechFunctions.finishAnnouncer
                SpeechFunctions.player = created
                let playing = created.play()

                return BridgeResponse.success(data: ["playing": playing])
            } catch {
                SpeechFunctions.player = nil

                return BridgeResponse.error(code: "PLAYBACK_FAILED", message: error.localizedDescription)
            }
        }
    }

    /// Pause the clip currently playing, keeping its position.
    /// Returns:
    ///   - paused: boolean — false when nothing was playing
    class Pause: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            guard let current = SpeechFunctions.player, current.isPlaying else {
                return BridgeResponse.success(data: ["paused": false])
            }

            current.pause()

            return BridgeResponse.success(data: ["paused": true])
        }
    }

    /// Carry on playing a paused clip from where it stopped.
    /// Returns:
    ///   - playing: boolean — false when there was no paused clip
    class Resume: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            guard let current = SpeechFunctions.player, !current.isPlaying else {
                return BridgeResponse.success(data: ["playing": false])
            }

            return BridgeResponse.success(data: ["playing": current.play()])
        }
    }

    /// Stop any speech or audio clip currently playing.
    class Stop: BridgeFunction {
        func execute(parameters: [String: Any]) throws -> [String: Any] {
            SpeechFunctions.player?.stop()
            SpeechFunctions.player = nil

            let stopped = SpeechFunctions.synthesizer.stopSpeaking(at: .immediate)
            return BridgeResponse.success(data: ["stopped" : stopped])
        }
    }
}
