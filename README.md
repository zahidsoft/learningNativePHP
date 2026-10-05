# learningNativePHP

## Known issue: audio files 404 in the built Android app (fix + how to reapply)

**Symptom:** Recorded `.mp3` audio (letters, phonemes) plays fine in the
browser but is silent in the installed Android app — it silently falls
through to native TTS or stays silent entirely.

**Root cause:** `WebViewManager.kt`'s request router only serves known file
extensions directly from disk (`isStaticAssetExtension()`). `.mp3`/`.wav`
were missing from that allowlist, so every `/audio/**` request fell through
to the full PHP/Laravel bootstrap instead — which has no route for raw
`public/` files, so it 404s.

**Fix — 2 changes**, in class `WebViewManager`, file:
`nativephp/android/app/src/main/java/com/nativephp/mobile/network/WebViewManager.kt`

1. In `isStaticAssetExtension()`, add to the `staticExtensions` list:
   ```kotlin
   ".mp3", ".wav", ".m4a", ".aac", ".ogg"
   ```
2. In `shouldInterceptRequest`'s `when` condition, add a new branch alongside
   the existing `/js/ /css/ /fonts/ /images/` checks:
   ```kotlin
   url.contains("/audio/") ->
   ```

**This is an upstream `nativephp/mobile` bug**, not something specific to
this project — the same gap exists in the package's own template at
`vendor/nativephp/mobile/resources/androidstudio/app/src/main/java/com/nativephp/mobile/network/WebViewManager.kt`
(already patched here too, so `php artisan native:install` regenerates a
working copy).

**Why this can come back:** `vendor/` isn't committed to git. If
`composer update nativephp/mobile` ever runs, it fetches the original
(unpatched) package and overwrites the vendor copy. If `php artisan
native:install` then runs after that, it regenerates
`nativephp/android/...` from the now-reverted template — bringing the bug
back. If that happens, reapply both changes above to both files. Consider
reporting this upstream to NativePHP so it gets fixed for good.
