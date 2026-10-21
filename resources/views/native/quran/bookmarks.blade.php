@use('App\Icons\Android')
@use('App\Icons\Ios')
@use('App\Services\Quran\Bangla')

<native:top-bar title="বুকমার্ক" :subtitle="Bangla::number($bookmarks->count()) . 'টি আয়াত'" back :background-color="theme('quran')" :text-color="theme('on-quran')" font-name="headline" />

<native:scroll-view native:key="bookmarks-screen" class="w-full h-full bg-theme-quran-paper">
    <native:column class="w-full px-4 pt-4 pb-8 gap-2">
        @forelse ($bookmarks as $bookmark)
            <native:row native:key="bookmark-{{ $bookmark->id }}" class="w-full pl-4 pr-2 py-3 gap-3 rounded-[18] bg-theme-surface items-center">
                <native:pressable @navigate="'/quran/surah/' . $bookmark->surah . '/' . $bookmark->ayah_in_surah" :press-scale="0.98" class="flex-1">
                    <native:row class="w-full items-center gap-3">
                        <native:icon :ios="Ios::BookmarkFill" :android="Android::Bookmark" :size="22" class="text-theme-quran-gold" />
                        <native:column class="flex-1 gap-1">
                            <native:text font="headline" class="text-base text-theme-on-surface">সূরা {{ Bangla::surahName($bookmark->surah) }}</native:text>
                            <native:text font="bangla" class="text-xs text-theme-on-surface-variant">সূরা {{ Bangla::number($bookmark->surah) }} · আয়াত {{ Bangla::number($bookmark->ayah_in_surah) }}</native:text>
                        </native:column>
                    </native:row>
                </native:pressable>
                <native:pressable @tap="remove({{ $bookmark->id }})" :press-scale="0.9" class="w-[40] h-[40] rounded-full items-center justify-center" a11y-label="মুছে ফেলুন">
                    <native:icon :ios="Ios::Trash" :android="Android::Delete" :size="20" class="text-theme-destructive" />
                </native:pressable>
            </native:row>
        @empty
            <native:column native:key="no-bookmarks" class="w-full p-6 gap-3 rounded-[24] bg-theme-surface items-center">
                <native:icon :ios="Ios::Bookmark" :android="Android::BookmarkBorder" :size="44" class="text-theme-quran" />
                <native:text font="bangla" class="text-base text-theme-on-surface text-center">এখনো কোনো আয়াত সংরক্ষণ করা হয়নি</native:text>
                <native:text font="bangla" class="text-sm text-theme-on-surface-variant text-center">সূরা পড়ার সময় আয়াতের পাশের বুকমার্ক চিহ্নে চাপ দিন</native:text>
            </native:column>
        @endforelse
    </native:column>
</native:scroll-view>
