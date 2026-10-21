@use('App\Icons\Android')
@use('App\Icons\Ios')
@use('App\Services\Quran\Bangla')
@use('App\Services\Quran\Mushaf')

{{--
    Every element that appears, disappears or changes kind between renders
    carries its own native:key. Without one the renderer matches nodes by
    position, and a spinner taking an icon's place crashes it on Android.
--}}
<native:top-bar
    :title="Bangla::surahName($number)"
    :subtitle="$surah === null ? '' : $surah['englishName'] . ' · ' . Bangla::number($surah['numberOfAyahs']) . ' আয়াত'"
    back
    :background-color="theme('quran')"
    :text-color="theme('on-quran')"
    font-name="headline"
/>

<native:scroll-view native:key="surah-reader-screen" class="w-full h-full bg-theme-quran-paper">
    <native:column class="w-full px-4 pt-4 pb-6 gap-3">
        @if ($error !== null)
            <native:column native:key="load-error" class="w-full p-5 gap-3 rounded-[24] bg-theme-surface items-center">
                <native:icon :ios="Ios::WifiSlash" :android="Android::WifiOff" :size="40" class="text-theme-destructive" />
                <native:text font="bangla" class="text-base text-theme-on-surface text-center">{{ $error }}</native:text>
                <native:pressable @tap="load" :press-scale="0.95" class="px-6 py-3 rounded-full bg-theme-quran">
                    <native:text font="bangla" class="text-base text-theme-on-quran">আবার চেষ্টা করুন</native:text>
                </native:pressable>
            </native:column>
        @endif

        @if ($surah !== null)
            <native:column native:key="surah-heading" class="w-full p-5 gap-2 rounded-[28] items-center bg-gradient-to-b from-theme-quran to-theme-quran-light">
                <native:text font="quran" class="text-4xl text-theme-on-quran text-center">{{ $surah['name'] }}</native:text>
                <native:text font="headline" class="text-lg text-theme-on-quran text-center">সূরা {{ Bangla::surahName($number) }}</native:text>
                <native:text font="label" class="text-sm text-theme-on-quran/80 text-center">{{ $surah['englishName'] }} · {{ $surah['englishNameTranslation'] }}</native:text>
                <native:text font="bangla" class="text-xs text-theme-on-quran/70 text-center">{{ Bangla::revelation($surah['revelationType']) }} · অনুবাদ: {{ $translation->translator() }}</native:text>
                @if (Mushaf::opensWithBismillah($number) && $startAyah === 1)
                    <native:text native:key="bismillah" font="quran" class="text-2xl pt-2 text-theme-quran-gold text-center">{{ Mushaf::BISMILLAH }}</native:text>
                @endif
            </native:column>

            @if ($startAyah > 1)
                <native:pressable native:key="show-earlier" @tap="showFromStart" :press-scale="0.97" class="w-full py-3 rounded-[18] bg-theme-quran/10 items-center">
                    <native:row class="items-center gap-2">
                        <native:icon :ios="Ios::ArrowUp" :android="Android::ArrowUpward" :size="18" class="text-theme-quran" />
                        <native:text font="bangla" class="text-sm text-theme-quran">আগের আয়াতগুলো দেখুন</native:text>
                    </native:row>
                </native:pressable>
            @endif

            @foreach ($ayahs as $position => $ayah)
                @continue($ayah['numberInSurah'] < $startAyah)
                @php
                    $isPlaying = $playingAyah === $ayah['number'];
                    $isLoading = $loadingAyah === $ayah['number'];
                    $isSaved = in_array($ayah['number'], $bookmarked, true);
                    $state = $isLoading ? 'loading' : ($isPlaying ? 'playing' : 'idle');
                @endphp
                <native:column
                    native:key="ayah-{{ $ayah['number'] }}"
                    :class="'w-full p-4 gap-3 rounded-[22] border-2 ' . ($isPlaying || $isLoading ? 'bg-theme-quran/5 border-theme-quran-gold' : 'bg-theme-surface border-transparent')"
                >
                    <native:row native:key="ayah-{{ $ayah['number'] }}-actions" class="w-full items-center gap-2">
                        <native:column native:key="number" class="w-[38] h-[38] rounded-full border-2 border-theme-quran-gold items-center justify-center">
                            <native:text font="headline" class="text-sm text-theme-quran">{{ Bangla::number($ayah['numberInSurah']) }}</native:text>
                        </native:column>
                        @if ($ayah['sajda'])
                            <native:column native:key="sajda" class="px-2 py-1 rounded-full bg-theme-quran-gold/20">
                                <native:text font="bangla" class="text-xs text-theme-quran">সিজদা</native:text>
                            </native:column>
                        @endif
                        <native:spacer native:key="gap" />
                        <native:pressable native:key="play" @tap="playAyah({{ $ayah['number'] }})" :press-scale="0.9" class="w-[40] h-[40] rounded-full bg-theme-quran/10 items-center justify-center" a11y-label="আয়াত {{ $ayah['numberInSurah'] }} শুনুন">
                            @if ($state === 'loading')
                                <native:activity-indicator native:key="play-loading" size="sm" :color="theme('quran')" />
                            @elseif ($state === 'playing')
                                <native:icon native:key="play-playing" :ios="Ios::SpeakerWave2Fill" :android="Android::VolumeUp" :size="20" class="text-theme-quran" />
                            @else
                                <native:icon native:key="play-idle" :ios="Ios::PlayFill" :android="Android::PlayArrow" :size="20" class="text-theme-quran" />
                            @endif
                        </native:pressable>
                        <native:pressable native:key="bookmark" @tap="toggleBookmark({{ $position }})" :press-scale="0.9" class="w-[40] h-[40] rounded-full bg-theme-quran/10 items-center justify-center" a11y-label="বুকমার্ক">
                            @if ($isSaved)
                                <native:icon native:key="bookmark-saved" :ios="Ios::BookmarkFill" :android="Android::Bookmark" :size="20" class="text-theme-quran-gold" />
                            @else
                                <native:icon native:key="bookmark-empty" :ios="Ios::Bookmark" :android="Android::BookmarkBorder" :size="20" class="text-theme-quran" />
                            @endif
                        </native:pressable>
                    </native:row>

                    <native:text native:key="arabic" font="quran" class="w-full text-3xl leading-loose text-right text-theme-on-surface">{{ $ayah['text'] }}</native:text>
                    <native:divider native:key="divider" class="border-theme-outline-variant" />
                    <native:text native:key="translation" font="bangla" class="w-full text-base leading-relaxed text-theme-on-surface-variant">{{ $ayah['translation'] }}</native:text>
                </native:column>
            @endforeach

            {{-- One pressable either way, so finishing the surah never swaps the element's kind --}}
            <native:pressable
                native:key="mark-read"
                @tap="markAsRead"
                :press-scale="0.97"
                :class="'w-full py-4 rounded-[22] items-center ' . ($isCountedInKhatam ? 'bg-theme-success/15' : 'bg-theme-quran')"
            >
                <native:row class="items-center gap-2">
                    <native:icon :ios="Ios::CheckmarkCircleFill" :android="Android::CheckCircle" :size="22" :class="$isCountedInKhatam ? 'text-theme-success' : 'text-theme-on-quran'" />
                    <native:text font="bangla" :class="'text-base ' . ($isCountedInKhatam ? 'text-theme-on-surface' : 'text-theme-on-quran')">{{ $isCountedInKhatam ? 'এই সূরা খতমের হিসাবে যোগ হয়েছে' : 'সূরা পড়া শেষ' }}</native:text>
                </native:row>
            </native:pressable>

            <native:row native:key="surah-navigation" class="w-full gap-3">
                @if ($number < Mushaf::SURAH_COUNT)
                    <native:pressable native:key="next-surah" @tap="openSurah({{ $number + 1 }})" :press-scale="0.97" class="flex-1 p-3 gap-1 rounded-[18] bg-theme-surface">
                        <native:row class="w-full items-center gap-1">
                            <native:icon :ios="Ios::ChevronLeft" :android="Android::ChevronLeft" :size="18" class="text-theme-quran" />
                            <native:text font="bangla" class="text-xs text-theme-on-surface-variant">পরের সূরা</native:text>
                        </native:row>
                        <native:text font="headline" class="text-base text-theme-quran" :max-lines="1">{{ Bangla::surahName($number + 1) }}</native:text>
                    </native:pressable>
                @endif
                @if ($number > 1)
                    <native:pressable native:key="previous-surah" @tap="openSurah({{ $number - 1 }})" :press-scale="0.97" class="flex-1 p-3 gap-1 rounded-[18] bg-theme-surface items-end">
                        <native:row class="items-center gap-1">
                            <native:text font="bangla" class="text-xs text-theme-on-surface-variant">আগের সূরা</native:text>
                            <native:icon :ios="Ios::ChevronRight" :android="Android::ChevronRight" :size="18" class="text-theme-quran" />
                        </native:row>
                        <native:text font="headline" class="text-base text-theme-quran" :max-lines="1">{{ Bangla::surahName($number - 1) }}</native:text>
                    </native:pressable>
                @endif
            </native:row>
        @endif
    </native:column>
</native:scroll-view>

@if ($surah !== null)
    @php
        $current = collect($ayahs)->firstWhere('number', $playingAyah ?? $loadingAyah);
        $status = match (true) {
            $loadingAyah !== null => 'তিলাওয়াত ডাউনলোড হচ্ছে…',
            $current !== null => 'আয়াত ' . Bangla::number($current['numberInSurah']) . ($isPaused ? ' থেমে আছে' : ' বাজছে'),
            default => 'পুরো সূরা শুনুন',
        };
        $isActive = $playingAyah !== null || $loadingAyah !== null;
    @endphp
    <native:bottom-bar>
        <native:column native:key="player" class="w-full px-4 py-3 gap-2 bg-theme-surface">
            @if ($audioError !== null)
                <native:text native:key="audio-error" font="bangla" class="text-xs text-theme-destructive">{{ $audioError }}</native:text>
            @endif
            <native:row native:key="player-controls" class="w-full items-center gap-3">
                {{-- The whole left side toggles playback, not only the round button --}}
                <native:pressable native:key="toggle" @tap="togglePause" :press-scale="0.97" class="flex-1">
                    <native:row class="w-full items-center gap-3">
                        <native:column class="w-[52] h-[52] rounded-full bg-theme-quran items-center justify-center">
                            @if ($playingAyah !== null && ! $isPaused)
                                <native:icon native:key="pause-icon" :ios="Ios::PauseFill" :android="Android::Pause" :size="26" class="text-theme-on-quran" />
                            @else
                                <native:icon native:key="play-icon" :ios="Ios::PlayFill" :android="Android::PlayArrow" :size="26" class="text-theme-on-quran" />
                            @endif
                        </native:column>
                        <native:column class="flex-1 gap-1">
                            <native:text font="bangla" class="text-sm text-theme-on-surface">{{ $status }}</native:text>
                            <native:text font="bangla" class="text-xs text-theme-on-surface-variant">একবার ডাউনলোড হলে পরে নেট ছাড়াই বাজবে</native:text>
                        </native:column>
                    </native:row>
                </native:pressable>
                @if ($isActive)
                    <native:pressable native:key="stop" @tap="stopAudio" :press-scale="0.92" class="w-[44] h-[44] rounded-full bg-theme-quran/10 items-center justify-center" a11y-label="বন্ধ করুন">
                        <native:icon :ios="Ios::StopFill" :android="Android::Stop" :size="22" class="text-theme-quran" />
                    </native:pressable>
                @endif
            </native:row>
        </native:column>
    </native:bottom-bar>
@endif
