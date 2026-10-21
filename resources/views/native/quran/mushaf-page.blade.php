@use('App\Icons\Android')
@use('App\Icons\Ios')
@use('App\Services\Quran\Bangla')
@use('App\Services\Quran\Mushaf')

<native:top-bar
    :title="'পৃষ্ঠা ' . Bangla::number($page)"
    :subtitle="'পারা ' . Bangla::number($juz)"
    back
    :background-color="theme('quran')"
    :text-color="theme('on-quran')"
    font-name="headline"
>
    <native:top-bar-action id="juz" label="পারা বাছুন" @tap="toggleJuzPicker" :ios-icon="Ios::ListBullet" :android-icon="Android::List" />
</native:top-bar>

<native:column native:key="mushaf-page-screen" class="w-full h-full bg-theme-quran-paper">
    @if ($isPickingJuz)
        <native:column native:key="juz-picker" class="w-full px-4 py-3 gap-2 bg-theme-surface">
            <native:text font="bangla" class="text-sm text-theme-on-surface-variant">কোন পারায় যাবেন?</native:text>
            @foreach (array_chunk(range(1, Mushaf::JUZ_COUNT), 6) as $juzRow)
                <native:row class="w-full gap-2">
                    @foreach ($juzRow as $juzNumber)
                        <native:pressable
                            @tap="openJuz({{ $juzNumber }})"
                            :press-scale="0.92"
                            native:key="juz-{{ $juzNumber }}"
                            :class="'flex-1 py-2 rounded-[12] items-center ' . ($juzNumber === $juz ? 'bg-theme-quran' : 'bg-theme-quran/10')"
                        >
                            <native:text font="headline" :class="'text-sm ' . ($juzNumber === $juz ? 'text-theme-on-quran' : 'text-theme-quran')">{{ Bangla::number($juzNumber) }}</native:text>
                        </native:pressable>
                    @endforeach
                </native:row>
            @endforeach
        </native:column>
    @endif

    <native:scroll-view native:key="page" class="w-full flex-1">
        <native:column class="w-full px-4 pt-4 pb-6 gap-4">
            @if ($error !== null)
                <native:column native:key="load-error" class="w-full p-5 gap-3 rounded-[24] bg-theme-surface items-center">
                    <native:icon :ios="Ios::WifiSlash" :android="Android::WifiOff" :size="40" class="text-theme-destructive" />
                    <native:text font="bangla" class="text-base text-theme-on-surface text-center">{{ $error }}</native:text>
                    <native:pressable @tap="retry" :press-scale="0.95" class="px-6 py-3 rounded-full bg-theme-quran">
                        <native:text font="bangla" class="text-base text-theme-on-quran">আবার চেষ্টা করুন</native:text>
                    </native:pressable>
                </native:column>
            @endif

            {{-- A framed page, like a printed muṣḥaf --}}
            @if ($sections !== [])
                <native:column native:key="page-{{ $page }}" class="w-full p-4 gap-4 rounded-[20] border-2 border-theme-quran-gold bg-theme-surface">
                    @foreach ($sections as $section)
                        @if ($section['startsSurah'])
                            <native:column native:key="heading-{{ $section['surah'] }}" class="w-full py-3 gap-1 rounded-[14] items-center bg-theme-quran/10 border border-theme-quran-gold">
                                <native:text font="quran" class="text-2xl text-theme-quran text-center">{{ $section['surahName'] }}</native:text>
                                @if (Mushaf::opensWithBismillah($section['surah']))
                                    <native:text font="quran" class="text-xl text-theme-on-surface text-center">{{ Mushaf::BISMILLAH }}</native:text>
                                @endif
                            </native:column>
                        @endif

                        <native:text font="quran" class="w-full text-2xl leading-loose text-right text-theme-on-surface" native:key="section-{{ $section['surah'] }}">
                            @foreach ($section['ayahs'] as $ayah)
                                {{ $ayah['text'] }} <native:text class="text-theme-quran-gold">{{ Mushaf::ayahMarker($ayah['numberInSurah']) }}</native:text>
                            @endforeach
                        </native:text>
                    @endforeach
                </native:column>

                <native:text native:key="surah-names" font="bangla" class="w-full text-xs text-theme-on-surface-variant text-center">{{ implode(' · ', array_map(fn (int $surah): string => 'সূরা ' . Bangla::surahName($surah), $surahNumbers)) }}</native:text>
            @endif
        </native:column>
    </native:scroll-view>

    {{-- Arabic reads right to left, so the next page sits on the left --}}
    <native:row class="w-full px-4 pt-3 pb-5 gap-3 bg-theme-quran-paper">
        <native:pressable @tap="nextPage" :press-scale="0.96" :class="'flex-1 py-4 rounded-[18] items-center ' . ($isLastPage ? 'bg-theme-quran/40' : 'bg-theme-quran')">
            <native:row class="items-center gap-2">
                <native:icon :ios="Ios::ChevronLeft" :android="Android::ChevronLeft" :size="20" class="text-theme-on-quran" />
                <native:text font="bangla" class="text-base text-theme-on-quran">পরের পৃষ্ঠা</native:text>
            </native:row>
        </native:pressable>
        <native:pressable @tap="previousPage" :press-scale="0.96" :class="'flex-1 py-4 rounded-[18] items-center ' . ($isFirstPage ? 'bg-theme-surface/50' : 'bg-theme-surface')">
            <native:row class="items-center gap-2">
                <native:text font="bangla" class="text-base text-theme-quran">আগের পৃষ্ঠা</native:text>
                <native:icon :ios="Ios::ChevronRight" :android="Android::ChevronRight" :size="20" class="text-theme-quran" />
            </native:row>
        </native:pressable>
    </native:row>
</native:column>
