@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar title="Quiz Time" :subtitle="'Score ' . $score" back :background-color="theme('quiz')" :text-color="theme('on-quiz')" font-name="headline" />

<native:scroll-view class="w-full h-full bg-theme-quiz">
    @if ($isFinished)
        <native:column class="w-full px-6 pt-10 pb-8 gap-6 items-center">
            <native:column class="w-[120] h-[120] rounded-full bg-theme-highlight items-center justify-center">
                <native:icon :ios="Ios::TrophyFill" :android="Android::EmojiEvents" :size="72" class="text-theme-on-highlight" />
            </native:column>

            <native:column class="items-center gap-2">
                <native:text font="headline" class="text-4xl text-theme-on-quiz text-center">{{ $score >= 7 ? 'Great job!' : 'Good try!' }}</native:text>
                <native:text class="text-lg text-theme-quiz-muted text-center">You got {{ $score }} out of {{ $questionCount }}</native:text>
            </native:column>

            <native:row class="w-full flex-wrap justify-center gap-2">
                @for ($star = 1; $star <= $questionCount; $star++)
                    <native:icon :ios="Ios::StarFill" :android="Android::Star" :size="28" :class="$star <= $score ? 'text-theme-highlight' : 'text-theme-on-quiz/20'" />
                @endfor
            </native:row>

            <native:column class="w-full gap-3 pt-2">
                <native:pressable @tap="restart" :press-scale="0.96" class="w-full py-4 rounded-2xl bg-theme-highlight shadow-md items-center justify-center">
                    <native:row class="items-center gap-2">
                        <native:icon :ios="Ios::ArrowClockwise" :android="Android::Refresh" :size="22" class="text-theme-on-highlight" />
                        <native:text font="headline" class="text-lg text-theme-on-highlight">Play again</native:text>
                    </native:row>
                </native:pressable>
                <native:pressable @navigate.back :press-scale="0.96" class="w-full py-4 rounded-2xl bg-theme-on-quiz/20 items-center justify-center">
                    <native:text font="headline" class="text-lg text-theme-on-quiz">Back to learning</native:text>
                </native:pressable>
            </native:column>
        </native:column>
    @else
        <native:column class="w-full px-5 pt-4 pb-8 gap-5">
            <native:column class="w-full gap-2">
                <native:row class="w-full gap-1">
                    @for ($step = 1; $step <= $questionCount; $step++)
                        <native:column :class="'flex-1 h-[8] rounded-full ' . ($step <= $questionNumber ? 'bg-theme-highlight' : 'bg-theme-on-quiz/20')"></native:column>
                    @endfor
                </native:row>
                <native:text font="label" class="text-sm text-theme-quiz-muted">Question {{ $questionNumber }} of {{ $questionCount }}</native:text>
            </native:column>

            <native:text font="label" class="text-lg text-theme-quiz-muted text-center pt-2">Which one is this letter?</native:text>

            <native:column class="w-[190] h-[190] rounded-[48] bg-theme-surface shadow-lg items-center justify-center self-center">
                <native:text :font="$question['font']" class="text-[110] text-theme-quiz">{{ $question['character'] }}</native:text>
            </native:column>

            <native:column class="w-full gap-3">
                @foreach (array_chunk($question['options'], 2, true) as $row)
                    <native:row class="w-full gap-3">
                        @foreach ($row as $position => $option)
                            @php
                                $isAnswer = $option === $question['answer'];
                                $optionClasses = match (true) {
                                    $picked === null => 'bg-theme-surface',
                                    $isAnswer => 'bg-theme-success',
                                    $option === $picked => 'bg-theme-destructive',
                                    default => 'bg-theme-surface/40',
                                };
                                $optionTextClasses = match (true) {
                                    $picked === null => 'text-theme-on-highlight',
                                    $isAnswer => 'text-theme-on-success',
                                    $option === $picked => 'text-theme-on-destructive',
                                    default => 'text-theme-on-highlight',
                                };
                            @endphp
                            <native:pressable
                                @tap="pick({{ $position }})"
                                :press-scale="0.95"
                                native:key="option-{{ $questionNumber }}-{{ $position }}"
                                :class="'flex-1 px-2 py-5 rounded-2xl shadow-sm items-center justify-center ' . $optionClasses"
                            >
                                <native:text font="headline" :class="'text-lg text-center ' . $optionTextClasses">{{ $option }}</native:text>
                            </native:pressable>
                        @endforeach
                    </native:row>
                @endforeach
            </native:column>

            @if ($picked !== null)
                <native:row class="w-full p-4 rounded-3xl bg-theme-on-quiz/20 items-center gap-3">
                    @if ($isCorrect)
                        <native:icon :ios="Ios::Checkmark" :android="Android::Check" :size="28" class="text-theme-success" />
                    @else
                        <native:icon :ios="Ios::Xmark" :android="Android::Close" :size="28" class="text-theme-destructive" />
                    @endif
                    <native:text font="headline" class="flex-1 text-base text-theme-on-quiz">{{ $isCorrect ? 'Correct! Well done' : 'It was ' . $question['answer'] }}</native:text>
                    <native:pressable @tap="next" :press-scale="0.95" class="px-6 py-3 rounded-2xl bg-theme-highlight">
                        <native:text font="headline" class="text-base text-theme-on-highlight">{{ $questionNumber >= $questionCount ? 'Finish' : 'Next' }}</native:text>
                    </native:pressable>
                </native:row>
            @endif
        </native:column>
    @endif
</native:scroll-view>
