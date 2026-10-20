@use('App\Icons\Android')
@use('App\Icons\Ios')
@use('App\Services\Quran\Bangla')

<native:top-bar title="খতমের হিসাব" back :background-color="theme('quran')" :text-color="theme('on-quran')" font-name="headline" />

<native:scroll-view class="w-full h-full bg-theme-quran-paper">
    <native:column class="w-full px-4 pt-4 pb-8 gap-4">
        @if ($error !== null)
            <native:column native:key="load-error" class="w-full p-5 gap-3 rounded-[24] bg-theme-surface items-center">
                <native:icon :ios="Ios::WifiSlash" :android="Android::WifiOff" :size="40" class="text-theme-destructive" />
                <native:text font="bangla" class="text-base text-theme-on-surface text-center">{{ $error }}</native:text>
                <native:pressable @tap="retry" :press-scale="0.95" class="px-6 py-3 rounded-full bg-theme-quran">
                    <native:text font="bangla" class="text-base text-theme-on-quran">আবার চেষ্টা করুন</native:text>
                </native:pressable>
            </native:column>
        @endif

        @if ($summary !== null)
            @php($khatam = $summary['khatam'])

            <native:column native:key="khatam-{{ $khatam->id }}" class="w-full p-5 gap-3 rounded-[28] bg-gradient-to-b from-theme-quran to-theme-quran-light">
                @if ($khatam->isComplete())
                    <native:row native:key="complete" class="w-full items-center gap-3">
                        <native:icon :ios="Ios::CheckmarkSealFill" :android="Android::Verified" :size="40" class="text-theme-quran-gold" />
                        <native:column class="flex-1 gap-1">
                            <native:text font="headline" class="text-2xl text-theme-on-quran">আলহামদুলিল্লাহ!</native:text>
                            <native:text font="bangla" class="text-sm text-theme-on-quran/80">খতম সম্পন্ন হয়েছে</native:text>
                        </native:column>
                    </native:row>
                    <native:pressable native:key="start-new" @tap="startNew" :press-scale="0.96" class="w-full py-3 rounded-[16] bg-theme-quran-gold items-center">
                        <native:text font="bangla" class="text-base text-theme-on-quran">নতুন খতম শুরু করুন</native:text>
                    </native:pressable>
                @else
                    <native:row native:key="percent" class="w-full items-end gap-2">
                        <native:text font="headline" class="text-5xl text-theme-on-quran">{{ Bangla::number($summary['percent']) }}%</native:text>
                        <native:text font="bangla" class="pb-2 text-sm text-theme-on-quran/80">সম্পন্ন</native:text>
                    </native:row>
                    <native:progress-bar native:key="progress" :value="$summary['read'] / $summary['total']" :color="theme('quran-gold')" class="w-full h-[8] rounded-full" />
                    <native:text native:key="read-count" font="bangla" class="text-sm text-theme-on-quran/80">{{ Bangla::number($summary['read']) }} / {{ Bangla::number($summary['total']) }} আয়াত পড়া হয়েছে</native:text>
                @endif

                @if ($summary['completedCount'] > 0)
                    <native:text native:key="completed-count" font="bangla" class="text-xs text-theme-on-quran/70">এ পর্যন্ত মোট {{ Bangla::number($summary['completedCount']) }}টি খতম</native:text>
                @endif
            </native:column>

            <native:text font="bangla" class="text-sm text-theme-on-surface-variant">মুসহাফে পাতা উল্টালে বা সূরা পড়া শেষ করলে এখানে যোগ হয়</native:text>

            @foreach (array_chunk($summary['juzs'], 3) as $juzRow)
                <native:row class="w-full gap-3">
                    @foreach ($juzRow as $juz)
                        @php($isDone = $juz['read'] >= $juz['total'])
                        <native:pressable
                            @navigate="'/quran/page/' . $juz['startPage']"
                            :press-scale="0.95"
                            native:key="juz-{{ $juz['juz'] }}"
                            :class="'flex-1 p-3 gap-2 rounded-[18] ' . ($isDone ? 'bg-theme-quran' : 'bg-theme-surface')"
                        >
                            <native:row class="w-full items-center">
                                <native:text font="headline" :class="'flex-1 text-base ' . ($isDone ? 'text-theme-on-quran' : 'text-theme-on-surface')">পারা {{ Bangla::number($juz['juz']) }}</native:text>
                                @if ($isDone)
                                    <native:icon native:key="done" :ios="Ios::CheckmarkCircleFill" :android="Android::CheckCircle" :size="18" class="text-theme-quran-gold" />
                                @endif
                            </native:row>
                            <native:progress-bar :value="$juz['read'] / max(1, $juz['total'])" :color="theme($isDone ? 'quran-gold' : 'quran')" class="w-full h-[5] rounded-full" />
                            <native:text font="bangla" :class="'text-xs ' . ($isDone ? 'text-theme-on-quran/80' : 'text-theme-on-surface-variant')">{{ Bangla::number($juz['read']) }}/{{ Bangla::number($juz['total']) }}</native:text>
                        </native:pressable>
                    @endforeach
                </native:row>
            @endforeach
        @endif
    </native:column>
</native:scroll-view>
