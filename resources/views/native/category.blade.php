@use('App\Enums\LearningCategory')
@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar :title="$category->title()" :subtitle="$category->subtitle()" back :background-color="$category->color()" text-color="#FFFFFF" font-name="headline" />

<native:scroll-view native:key="category-screen" :class="'w-full h-full bg-[' . $category->softColor() . ']'">
    <native:column class="w-full pb-8">
        <native:column :class="'w-full px-5 pt-2 pb-6 gap-4 rounded-b-[36] bg-[' . $category->color() . ']'">
            <native:row class="w-full items-center gap-3">
                <native:column class="flex-1 gap-1">
                    <native:text font="headline" class="text-2xl text-white">{{ $letterCount }} cards to learn</native:text>
                    <native:text class="text-sm text-white/80">{{ $learnedCount }} learned · tap a card to hear it</native:text>
                    <native:progress-bar :value="$learnedCount / $letterCount" color="#FFFFFF" class="w-full h-[6] mt-1 rounded-full" />
                </native:column>
                <native:column class="w-[56] h-[56] rounded-[18] bg-white/20 items-center justify-center">
                    <native:icon :ios="Ios::BookFill" :android="Android::MenuBook" :size="30" color="#FFFFFF" />
                </native:column>
            </native:row>

            <native:row class="w-full gap-3">
                <native:pressable @navigate="'/learn/' . $category->value . '/0'" :press-scale="0.96" class="flex-1 px-4 py-3 rounded-2xl bg-white">
                    <native:row class="w-full items-center justify-center gap-2">
                        <native:icon :ios="Ios::PlayFill" :android="Android::PlayArrow" :size="22" :color="$category->color()" />
                        <native:text font="headline" :class="'text-base text-[' . $category->color() . ']'">Start from the first</native:text>
                    </native:row>
                </native:pressable>
                <native:pressable @navigate='/quiz' :press-scale="0.96" class="px-5 py-3 rounded-2xl bg-theme-highlight">
                    <native:row class="items-center justify-center gap-2">
                        <native:icon :ios="Ios::QuestionmarkCircleFill" :android="Android::Help" :size="22" class="text-theme-on-highlight" />
                        <native:text font="headline" class="text-base text-theme-on-highlight">Quiz</native:text>
                    </native:row>
                </native:pressable>
            </native:row>
        </native:column>

        <native:column class="w-full px-4 pt-5 gap-3">
            @foreach ($tileRows as $row)
                <native:row class="w-full gap-3">
                    @foreach ($row as $index => $letter)
                        @php($letterProgress = $progress->get($letter->character))
                        <native:pressable
                            @navigate="'/learn/' . $category->value . '/' . $index"
                            :press-scale="0.94"
                            native:key="tile-{{ $category->value }}-{{ $index }}"
                            class="relative flex-1 px-1 py-3 gap-1 rounded-2xl bg-theme-surface shadow-sm items-center"
                        >
                            {{-- A star once the letter is answered right in the quiz, a tick once it has been heard --}}
                            @if ($letterProgress?->isMastered())
                                <native:icon native:key="mastered" :ios="Ios::StarFill" :android="Android::Star" :size="16" class="absolute top-[6] right-[6] text-theme-accent" />
                            @elseif ($letterProgress !== null)
                                <native:icon native:key="heard" :ios="Ios::CheckmarkCircleFill" :android="Android::CheckCircle" :size="16" class="absolute top-[6] right-[6] text-theme-success" />
                            @endif
                            <native:text native:key="character" :font="$category->font()" :class="($columns === 3 ? 'text-5xl' : 'text-4xl') . ' text-[' . LearningCategory::tileColor($index) . ']'">{{ $letter->character }}</native:text>
                            <native:text native:key="name" font="label" class="text-xs text-theme-on-surface-variant text-center" :max-lines="1">{{ $letter->name }}</native:text>
                        </native:pressable>
                    @endforeach

                    @for ($filler = count($row); $filler < $columns; $filler++)
                        <native:rect class="flex-1 h-[1] bg-transparent" />
                    @endfor
                </native:row>
            @endforeach
        </native:column>
    </native:column>
</native:scroll-view>
