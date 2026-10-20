@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar title="Spell It" :subtitle="$wordsSpelled . ' ' . ($wordsSpelled === 1 ? 'word' : 'words') . ' spelled'" back :background-color="theme('spell')" :text-color="theme('on-spell')" font-name="headline" />

<native:scroll-view class="w-full h-full bg-theme-spell-soft">
    <native:column class="w-full px-5 pt-4 pb-8 gap-5">
        <native:row class="w-full gap-2">
            @foreach ($levels as $option)
                <native:pressable
                    @tap="chooseLevel('{{ $option->value }}')"
                    :press-scale="0.95"
                    native:key="level-{{ $option->value }}"
                    :class="'flex-1 py-2 rounded-full items-center ' . ($option === $level ? 'bg-theme-spell' : 'bg-theme-surface')"
                >
                    <native:text font="label" :class="'text-sm ' . ($option === $level ? 'text-theme-on-spell' : 'text-theme-on-surface-variant')">{{ $option->label() }}</native:text>
                </native:pressable>
            @endforeach
        </native:row>

        {{-- The picture is the clue; tapping it says the word again --}}
        <native:pressable @tap="hearWord" :press-scale="0.97" class="relative w-full p-4 gap-3 rounded-[32] bg-theme-surface shadow-lg items-center">
            <native:image :src="$puzzle['image']" alt="Picture of the word" :fit="1" class="w-[170] h-[170]" />
            <native:row class="px-5 py-2 gap-2 rounded-full bg-theme-accent items-center">
                <native:icon :ios="Ios::SpeakerWave2Fill" :android="Android::VolumeUp" :size="20" class="text-theme-on-accent" />
                <native:text font="headline" class="text-base text-theme-on-accent">Hear the word</native:text>
            </native:row>
        </native:pressable>

        <native:row class="w-full flex-wrap justify-center gap-2">
            @foreach ($letters as $slot => $slotLetter)
                @php
                    $isFilled = $slot < $placedCount;
                    $isNext = $slot === $placedCount;
                @endphp
                <native:column
                    native:key="slot-{{ $slot }}"
                    :class="'w-[50] h-[60] rounded-2xl items-center justify-center border-2 ' . ($isFilled ? 'bg-theme-spell border-theme-spell' : ($isNext ? 'bg-theme-surface border-theme-spell' : 'bg-theme-surface border-theme-outline'))"
                >
                    @if ($isFilled)
                        <native:text font="headline" class="text-3xl text-theme-on-spell">{{ $slotLetter }}</native:text>
                    @elseif ($level->showsGhostLetters())
                        <native:text font="headline" class="text-3xl text-theme-spell/25">{{ $slotLetter }}</native:text>
                    @endif
                </native:column>
            @endforeach
        </native:row>

        @if ($isComplete)
            <native:column class="w-full p-5 gap-4 rounded-[28] items-center bg-gradient-to-b from-theme-spell to-theme-spell-light">
                <native:row class="items-center gap-2">
                    @for ($star = 0; $star < ($isPerfect ? 3 : 1); $star++)
                        <native:icon :ios="Ios::StarFill" :android="Android::Star" :size="34" class="text-theme-highlight" />
                    @endfor
                </native:row>
                <native:text font="headline" class="text-3xl text-theme-on-spell text-center">{{ $isPerfect ? 'Perfect!' : 'You did it!' }}</native:text>
                <native:text font="glyph" class="text-xl text-theme-on-spell/90 text-center">{{ ucfirst($puzzle['word']) }}</native:text>
                <native:pressable @tap="nextWord" :press-scale="0.96" class="w-full py-4 rounded-2xl bg-theme-highlight items-center">
                    <native:row class="items-center gap-2">
                        <native:text font="headline" class="text-lg text-theme-on-highlight">Next word</native:text>
                        <native:icon :ios="Ios::ArrowRight" :android="Android::ArrowForward" :size="22" class="text-theme-on-highlight" />
                    </native:row>
                </native:pressable>
            </native:column>
        @else
            <native:column class="w-full gap-3">
                <native:text font="label" class="text-sm text-theme-on-surface-variant text-center">Tap the letters in order</native:text>

                @foreach ($tileRows as $row)
                    <native:row class="w-full gap-3 justify-center">
                        @foreach ($row as $position => $tile)
                            @php
                                $isPlaced = in_array($position, $placedTiles, true);
                                $isHint = $position === $hintTile;
                            @endphp
                            <native:pressable
                                @tap="pickTile({{ $position }})"
                                :press-scale="0.9"
                                :press-translate-y="2"
                                native:key="tile-{{ $puzzle['word'] }}-{{ $position }}"
                                :class="'w-[66] h-[66] rounded-[20] items-center justify-center ' . ($isPlaced ? 'bg-theme-surface/40' : ($isHint ? 'bg-theme-highlight shadow-md' : 'bg-theme-surface shadow-md'))"
                            >
                                @unless ($isPlaced)
                                    <native:text font="headline" :class="'text-4xl ' . ($isHint ? 'text-theme-on-highlight' : 'text-theme-on-surface')">{{ $tile }}</native:text>
                                @endunless
                            </native:pressable>
                        @endforeach
                    </native:row>
                @endforeach
            </native:column>
        @endif
    </native:column>
</native:scroll-view>
