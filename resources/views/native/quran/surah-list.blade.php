@use('App\Icons\Android')
@use('App\Icons\Ios')
@use('App\Services\Quran\Bangla')

<native:top-bar title="আল-কুরআন" :subtitle="Bangla::number(count($surahs)) . 'টি সূরা'" back :background-color="theme('quran')" :text-color="theme('on-quran')" font-name="headline">
    <native:top-bar-action id="search" label="খুঁজুন" url="/quran/search" :ios-icon="Ios::Magnifyingglass" :android-icon="Android::Search" />
    <native:top-bar-action id="settings" label="সেটিংস" url="/quran/settings" :ios-icon="Ios::Gearshape" :android-icon="Android::Settings" />
</native:top-bar>

<native:scroll-view class="w-full h-full bg-theme-quran-paper">
    <native:column class="w-full px-4 pt-4 pb-8 gap-4">
        @if ($lastRead !== null)
            <native:pressable
                @navigate="'/quran/surah/' . $lastRead['surah'] . '/' . $lastRead['ayah']"
                :press-scale="0.97"
                class="relative w-full p-4 rounded-[24] shadow-md bg-gradient-to-r from-theme-quran to-theme-quran-light"
            >
                <native:circle class="absolute top-[-20] right-[-10] w-[110] h-[110] bg-white/10" />
                <native:row class="w-full items-center gap-4">
                    <native:column class="w-[52] h-[52] rounded-[18] bg-white/20 items-center justify-center">
                        <native:icon :ios="Ios::BookFill" :android="Android::MenuBook" :size="28" class="text-theme-on-quran" />
                    </native:column>
                    <native:column class="flex-1 gap-1">
                        <native:text font="bangla" class="text-xs text-theme-on-quran/80">পড়া চালিয়ে যান</native:text>
                        <native:text font="headline" class="text-xl text-theme-on-quran" :max-lines="1">{{ $lastRead['surahName'] }}</native:text>
                        <native:text font="bangla" class="text-xs text-theme-on-quran/80">আয়াত {{ Bangla::number($lastRead['ayah']) }}</native:text>
                    </native:column>
                    <native:icon :ios="Ios::ChevronRight" :android="Android::ChevronRight" :size="24" class="text-theme-on-quran" />
                </native:row>
            </native:pressable>
        @endif

        <native:row class="w-full gap-3">
            @foreach ([
                ['url' => '/quran/page/' . $lastPage, 'label' => 'মুসহাফ', 'ios' => Ios::BookPagesFill, 'android' => Android::AutoStories],
                ['url' => '/quran/khatam', 'label' => 'খতম', 'ios' => Ios::ChartPieFill, 'android' => Android::DonutLarge],
                ['url' => '/quran/bookmarks', 'label' => 'বুকমার্ক', 'ios' => Ios::BookmarkFill, 'android' => Android::Bookmark],
            ] as $shortcut)
                <native:pressable @navigate="$shortcut['url']" :press-scale="0.95" native:key="shortcut-{{ $shortcut['label'] }}" class="flex-1 py-3 gap-2 rounded-[20] bg-theme-surface shadow-sm items-center">
                    <native:column class="w-[42] h-[42] rounded-full bg-theme-quran/10 items-center justify-center">
                        <native:icon :ios="$shortcut['ios']" :android="$shortcut['android']" :size="22" class="text-theme-quran" />
                    </native:column>
                    <native:text font="bangla" class="text-sm text-theme-on-surface">{{ $shortcut['label'] }}</native:text>
                </native:pressable>
            @endforeach
        </native:row>

        <native:outlined-text-input native:model.debounce.300ms="filter" placeholder="সূরার নাম বা নম্বর" leading-icon="search" font="bangla" />

        @if ($error !== null)
            <native:column class="w-full p-5 gap-3 rounded-[24] bg-theme-surface items-center">
                <native:icon :ios="Ios::WifiSlash" :android="Android::WifiOff" :size="40" class="text-theme-destructive" />
                <native:text font="bangla" class="text-base text-theme-on-surface text-center">{{ $error }}</native:text>
                <native:pressable @tap="load" :press-scale="0.95" class="px-6 py-3 rounded-full bg-theme-quran">
                    <native:text font="bangla" class="text-base text-theme-on-quran">আবার চেষ্টা করুন</native:text>
                </native:pressable>
            </native:column>
        @endif

        <native:column class="w-full gap-2">
            @foreach ($shownSurahs as $surah)
                <native:pressable
                    @navigate="'/quran/surah/' . $surah['number']"
                    :press-scale="0.98"
                    native:key="surah-{{ $surah['number'] }}"
                    class="w-full px-4 py-3 rounded-[18] bg-theme-surface"
                >
                    <native:row class="w-full items-center gap-3">
                        <native:column class="w-[44] h-[44] rounded-[14] border-2 border-theme-quran-gold items-center justify-center">
                            <native:text font="headline" class="text-base text-theme-quran">{{ Bangla::number($surah['number']) }}</native:text>
                        </native:column>
                        <native:column class="flex-1 gap-1">
                            <native:text font="headline" class="text-base text-theme-on-surface" :max-lines="1">{{ $surah['englishName'] }}</native:text>
                            <native:text font="bangla" class="text-xs text-theme-on-surface-variant" :max-lines="1">{{ Bangla::revelation($surah['revelationType']) }} · {{ Bangla::number($surah['numberOfAyahs']) }} আয়াত</native:text>
                        </native:column>
                        <native:text font="quran" class="text-2xl text-theme-quran">{{ $surah['name'] }}</native:text>
                    </native:row>
                </native:pressable>
            @endforeach
        </native:column>
    </native:column>
</native:scroll-view>
