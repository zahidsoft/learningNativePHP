@use('App\Enums\LearningCategory')
@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar :title="$category->title()" :subtitle="($index + 1) . ' of ' . count($letters)" back :background-color="$category->color()" text-color="#FFFFFF" font-name="headline" />

<native:column :class="'w-full h-full bg-[' . $category->softColor() . ']'">
    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full px-5 pt-6 pb-6 gap-5 items-center">
            <native:pressable @tap="hear" :press-scale="0.95" class="w-[250] h-[250] rounded-[60] bg-theme-surface shadow-lg items-center justify-center">
                <native:text :font="$category->font()" :class="'text-[150] text-[' . $color . ']'">{{ $letter->character }}</native:text>
            </native:pressable>

            <native:column class="items-center gap-1">
                <native:text font="headline" class="text-4xl text-theme-on-surface text-center">{{ $letter->name }}</native:text>
                @if ($letter->word !== '')
                    <native:text font="glyph" class="text-lg text-theme-on-surface-variant text-center">{{ $letter->word }}</native:text>
                @endif
            </native:column>

            <native:pressable @tap="hear" :press-scale="0.95" class="px-8 py-4 rounded-full bg-theme-accent shadow-md">
                <native:row class="items-center gap-3">
                    <native:icon :ios="Ios::SpeakerWave2Fill" :android="Android::VolumeUp" :size="24" class="text-theme-on-accent" />
                    <native:text font="headline" class="text-lg text-theme-on-accent">Tap to hear</native:text>
                </native:row>
            </native:pressable>

            @if ($dotCount > 0)
                <native:column class="w-full p-4 gap-3 rounded-3xl bg-theme-surface items-center">
                    <native:text font="label" class="text-sm text-theme-on-surface-variant">Count the dots</native:text>
                    <native:row class="w-full flex-wrap justify-center gap-2">
                        @for ($dot = 0; $dot < $dotCount; $dot++)
                            <native:circle :class="'w-[26] h-[26] bg-[' . LearningCategory::tileColor($dot) . ']'" />
                        @endfor
                    </native:row>
                </native:column>
            @endif

            @if ($phonemes !== [])
                <native:column class="w-full p-4 gap-3 rounded-3xl bg-theme-surface items-center">
                    <native:text font="label" class="text-sm text-theme-on-surface-variant">Letter sound</native:text>
                    <native:row class="items-center justify-center gap-3">
                        @foreach ($phonemes as $position => $symbol)
                            <native:pressable @tap="playPhoneme({{ $position }})" :press-scale="0.92" :class="'px-5 py-3 rounded-2xl bg-[' . $category->softColor() . ']'">
                                <native:row class="items-center gap-2">
                                    <native:icon :ios="Ios::MusicNote" :android="Android::MusicNote" :size="20" :color="$category->color()" />
                                    <native:text font="glyph" :class="'text-2xl text-[' . $category->color() . ']'">/{{ $symbol }}/</native:text>
                                </native:row>
                            </native:pressable>
                        @endforeach
                    </native:row>
                </native:column>
            @endif

            @if ($words !== [])
                <native:column class="w-full gap-3">
                    <native:text font="label" class="text-sm text-theme-on-surface-variant">Words with {{ $letter->character }}</native:text>
                    <native:scroll-view :horizontal="true" class="w-full">
                        <native:row class="gap-3 pb-2">
                            @foreach ($words as $position => $word)
                                <native:pressable @tap="playWord({{ $position }})" :press-scale="0.94" native:key="word-{{ $word->text }}" class="w-[92] p-2 gap-2 rounded-2xl bg-theme-surface shadow-sm items-center">
                                    <native:image :src="$word->image" :alt="$word->text" :fit="2" class="w-[72] h-[72] rounded-xl" />
                                    <native:text font="label" class="text-xs text-theme-on-surface-variant text-center" :max-lines="1">{{ $word->text }}</native:text>
                                </native:pressable>
                            @endforeach
                        </native:row>
                    </native:scroll-view>
                </native:column>
            @endif

            <native:column class="w-full gap-3">
                <native:text font="label" class="text-sm text-theme-on-surface-variant">All in {{ $category->title() }}</native:text>
                <native:scroll-view :horizontal="true" class="w-full">
                    <native:row class="gap-2 pb-2">
                        @foreach ($letters as $position => $ribbonLetter)
                            <native:pressable
                                @tap="show({{ $position }})"
                                :press-scale="0.92"
                                native:key="ribbon-{{ $category->value }}-{{ $position }}"
                                :class="'w-[52] h-[52] rounded-2xl items-center justify-center ' . ($position === $index ? 'bg-[' . $category->color() . ']' : 'bg-theme-surface')"
                            >
                                <native:text :font="$category->font()" :class="'text-2xl ' . ($position === $index ? 'text-white' : 'text-theme-on-surface-variant')">{{ $ribbonLetter->character }}</native:text>
                            </native:pressable>
                        @endforeach
                    </native:row>
                </native:scroll-view>
            </native:column>
        </native:column>
    </native:scroll-view>

    <native:row class="w-full px-5 pt-3 pb-5 gap-3">
        <native:pressable @tap="previous" :press-scale="0.96" class="flex-1 py-4 rounded-2xl bg-theme-surface shadow-sm items-center justify-center">
            <native:text font="headline" class="text-lg text-theme-on-surface">Previous</native:text>
        </native:pressable>
        <native:pressable @tap="next" :press-scale="0.96" :class="'flex-1 py-4 rounded-2xl shadow-sm items-center justify-center bg-[' . $category->color() . ']'">
            <native:text font="headline" class="text-lg text-white">Next</native:text>
        </native:pressable>
    </native:row>
</native:column>
