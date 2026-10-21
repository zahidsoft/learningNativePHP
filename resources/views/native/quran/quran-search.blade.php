@use('App\Icons\Android')
@use('App\Icons\Ios')
@use('App\Services\Quran\Bangla')

<native:top-bar title="আয়াত খুঁজুন" :subtitle="'অনুবাদ: ' . $translation->translator()" back :background-color="theme('quran')" :text-color="theme('on-quran')" font-name="headline" />

<native:scroll-view native:key="quran-search-screen" class="w-full h-full bg-theme-quran-paper">
    <native:column class="w-full px-4 pt-4 pb-8 gap-3">
        <native:outlined-text-input
            native:model="query"
            @submit="search"
            placeholder="একটি শব্দ লিখুন, যেমন: ধৈর্য"
            leading-icon="search"
            font="bangla"
        />

        <native:pressable @tap="search" :press-scale="0.96" class="w-full py-3 rounded-[16] bg-theme-quran items-center">
            <native:text font="bangla" class="text-base text-theme-on-quran">খুঁজুন</native:text>
        </native:pressable>

        @if ($error !== null)
            <native:row native:key="search-error" class="w-full p-4 gap-3 rounded-[18] bg-theme-surface items-center">
                <native:icon :ios="Ios::WifiSlash" :android="Android::WifiOff" :size="26" class="text-theme-destructive" />
                <native:text font="bangla" class="flex-1 text-sm text-theme-on-surface">{{ $error }}</native:text>
            </native:row>
        @endif

        @if ($searchedFor !== '' && ! $isSearching && $error === null)
            <native:text native:key="result-count" font="bangla" class="text-sm text-theme-on-surface-variant">
                {{ $total === 0 ? '"' . $searchedFor . '" শব্দটি কোনো আয়াতে পাওয়া যায়নি' : '"' . $searchedFor . '" পাওয়া গেছে ' . Bangla::number($total) . 'টি আয়াতে' }}
            </native:text>
        @endif

        @foreach ($matches as $match)
            <native:pressable
                @navigate="'/quran/surah/' . $match['surah'] . '/' . $match['numberInSurah']"
                :press-scale="0.98"
                native:key="match-{{ $match['number'] }}"
                class="w-full p-4 gap-2 rounded-[18] bg-theme-surface"
            >
                <native:row class="w-full items-center gap-2">
                    <native:text font="headline" class="flex-1 text-sm text-theme-quran">সূরা {{ Bangla::surahName($match['surah']) }}</native:text>
                    <native:text font="bangla" class="text-xs text-theme-on-surface-variant">আয়াত {{ Bangla::number($match['numberInSurah']) }}</native:text>
                </native:row>
                <native:text font="bangla" class="w-full text-base leading-relaxed text-theme-on-surface">{{ $match['text'] }}</native:text>
            </native:pressable>
        @endforeach

        @if ($isSearching)
            <native:row native:key="searching" class="w-full py-4 justify-center">
                <native:activity-indicator :color="theme('quran')" />
            </native:row>
        @elseif ($hasMore)
            <native:pressable native:key="load-more" @tap="loadMore" :press-scale="0.97" class="w-full py-3 rounded-[16] bg-theme-quran/10 items-center">
                <native:text font="bangla" class="text-sm text-theme-quran">আরও দেখুন</native:text>
            </native:pressable>
        @endif
    </native:column>
</native:scroll-view>
