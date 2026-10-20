@use('App\Enums\Translation')
@use('App\Icons\Android')
@use('App\Icons\Ios')
@use('App\Services\Quran\Bangla')
@use('Illuminate\Support\Number')

<native:top-bar title="কুরআন সেটিংস" back :background-color="theme('quran')" :text-color="theme('on-quran')" font-name="headline" />

<native:scroll-view class="w-full h-full bg-theme-quran-paper">
    <native:column class="w-full px-4 pt-4 pb-8 gap-5">
        {{-- Translation --}}
        <native:column class="w-full gap-2">
            <native:text font="headline" class="text-lg text-theme-on-surface">বাংলা অনুবাদ</native:text>
            @foreach ($translations as $option)
                @php($isChosen = $option === $translation)
                <native:pressable
                    @tap="chooseTranslation('{{ $option->value }}')"
                    :press-scale="0.98"
                    native:key="translation-{{ $option->value }}"
                    :class="'w-full p-4 rounded-[18] border-2 ' . ($isChosen ? 'bg-theme-quran/10 border-theme-quran' : 'bg-theme-surface border-transparent')"
                >
                    <native:row class="w-full items-center gap-3">
                        @if ($isChosen)
                            <native:icon native:key="chosen" :ios="Ios::CheckmarkCircleFill" :android="Android::CheckCircle" :size="24" class="text-theme-quran" />
                        @else
                            <native:icon native:key="not-chosen" :ios="Ios::Circle" :android="Android::RadioButtonUnchecked" :size="24" class="text-theme-outline" />
                        @endif
                        <native:text native:key="name" font="bangla" class="flex-1 text-base text-theme-on-surface">{{ $option->translator() }}</native:text>
                        @if ($option === Translation::default())
                            <native:text native:key="default" font="bangla" class="text-xs text-theme-on-surface-variant">ডিফল্ট</native:text>
                        @endif
                    </native:row>
                </native:pressable>
            @endforeach
        </native:column>

        {{-- Reciter --}}
        <native:column class="w-full gap-2">
            <native:text font="headline" class="text-lg text-theme-on-surface">ক্বারী (তিলাওয়াত)</native:text>
            <native:text font="bangla" class="text-sm text-theme-on-surface-variant">শুনে দেখে পছন্দের ক্বারী বেছে নিন</native:text>

            @if ($error !== null)
                <native:row native:key="reciters-error" class="w-full p-4 gap-3 rounded-[18] bg-theme-surface items-center">
                    <native:icon :ios="Ios::WifiSlash" :android="Android::WifiOff" :size="24" class="text-theme-destructive" />
                    <native:text font="bangla" class="flex-1 text-sm text-theme-on-surface">{{ $error }}</native:text>
                    <native:pressable @tap="loadReciters" :press-scale="0.95" class="px-4 py-2 rounded-full bg-theme-quran">
                        <native:text font="bangla" class="text-sm text-theme-on-quran">আবার</native:text>
                    </native:pressable>
                </native:row>
            @endif

            @foreach ($reciters as $option)
                @php($isChosen = $option['identifier'] === $reciter)
                <native:row
                    native:key="reciter-{{ $option['identifier'] }}"
                    :class="'w-full pl-4 pr-2 py-3 gap-2 rounded-[18] items-center border-2 ' . ($isChosen ? 'bg-theme-quran/10 border-theme-quran' : 'bg-theme-surface border-transparent')"
                >
                    <native:pressable native:key="choose" @tap="chooseReciter('{{ $option['identifier'] }}')" :press-scale="0.98" class="flex-1">
                        <native:row native:key="choose-row" class="w-full items-center gap-3">
                            @if ($isChosen)
                                <native:icon native:key="chosen" :ios="Ios::CheckmarkCircleFill" :android="Android::CheckCircle" :size="24" class="text-theme-quran" />
                            @else
                                <native:icon native:key="not-chosen" :ios="Ios::Circle" :android="Android::RadioButtonUnchecked" :size="24" class="text-theme-outline" />
                            @endif
                            <native:column native:key="names" class="flex-1 gap-1">
                                <native:text font="headline" class="text-base text-theme-on-surface">{{ $option['englishName'] }}</native:text>
                                <native:text font="arabic" class="text-sm text-theme-on-surface-variant">{{ $option['name'] }}</native:text>
                            </native:column>
                        </native:row>
                    </native:pressable>
                    <native:pressable native:key="preview" @tap="preview('{{ $option['identifier'] }}')" :press-scale="0.9" class="w-[42] h-[42] rounded-full bg-theme-quran/10 items-center justify-center" a11y-label="শুনে দেখুন">
                        @if ($previewing === $option['identifier'])
                            <native:activity-indicator native:key="preview-loading" size="sm" :color="theme('quran')" />
                        @else
                            <native:icon native:key="preview-play" :ios="Ios::PlayFill" :android="Android::PlayArrow" :size="20" class="text-theme-quran" />
                        @endif
                    </native:pressable>
                </native:row>
            @endforeach
        </native:column>

        {{-- Downloaded recitations --}}
        <native:column class="w-full gap-2">
            <native:text font="headline" class="text-lg text-theme-on-surface">ডাউনলোড করা তিলাওয়াত</native:text>
            @forelse ($downloads as $identifier => $bytes)
                <native:row native:key="download-{{ $identifier }}" class="w-full pl-4 pr-2 py-3 gap-3 rounded-[18] bg-theme-surface items-center">
                    <native:icon :ios="Ios::ArrowDownCircleFill" :android="Android::DownloadDone" :size="22" class="text-theme-quran" />
                    <native:column class="flex-1 gap-1">
                        <native:text font="headline" class="text-base text-theme-on-surface">{{ $reciterNames[$identifier] ?? $identifier }}</native:text>
                        <native:text font="bangla" class="text-xs text-theme-on-surface-variant">{{ Number::fileSize($bytes, precision: 1) }}</native:text>
                    </native:column>
                    <native:pressable @tap="deleteDownloads('{{ $identifier }}')" :press-scale="0.9" class="px-3 py-2 rounded-full bg-theme-destructive/10">
                        <native:text font="bangla" class="text-sm text-theme-destructive">মুছুন</native:text>
                    </native:pressable>
                </native:row>
            @empty
                <native:text native:key="no-downloads" font="bangla" class="text-sm text-theme-on-surface-variant">এখনো কোনো তিলাওয়াত ডাউনলোড হয়নি। যে আয়াত শুনবেন, শুধু সেটাই ডাউনলোড হবে।</native:text>
            @endforelse
        </native:column>
    </native:column>
</native:scroll-view>
