@use('App\Enums\LearningCategory')
@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar title="Learn & Grow" subtitle="Small Steps, Big Future" :background-color="theme('primary')" :text-color="theme('on-primary')" font-name="headline" />

<native:scroll-view class="w-full h-full bg-theme-background">
    <native:column class="w-full pb-8">
        {{-- Header: gradient band with soft bubbles behind the greeting --}}
        <native:column class="relative w-full px-5 pt-3 pb-[64] rounded-b-[40] bg-gradient-to-b from-theme-primary to-theme-primary-light">
            <native:circle class="absolute top-[6] right-[18] w-[120] h-[120] bg-white/10" />
            <native:circle class="absolute top-[78] right-[112] w-[46] h-[46] bg-white/10" />
            <native:circle class="absolute top-[70] left-[8] w-[84] h-[84] bg-white/10" />

            <native:row class="w-full items-center gap-4">
                <native:column class="w-[68] h-[68] rounded-[22] bg-theme-highlight border-4 border-white items-center justify-center">
                    <native:icon :ios="Ios::StarFill" :android="Android::Star" :size="36" class="text-theme-primary" />
                </native:column>
                <native:column class="flex-1 gap-1">
                    <native:text font="headline" class="text-3xl text-theme-on-primary">{{ $greeting }}</native:text>
                    <native:text font="label" class="text-sm text-theme-on-primary/80">What shall we learn today?</native:text>
                </native:column>
            </native:row>
        </native:column>

        {{-- Featured card, pulled up so it overlaps the header --}}
        <native:column class="w-full px-4 -mt-[44]">
            <native:pressable
                @navigate="'/learn/' . $letterOfTheDay['category']->value . '/' . $letterOfTheDay['index']"
                :press-scale="0.97"
                class="w-full p-4 rounded-[28] bg-theme-surface shadow-lg"
            >
                <native:row class="w-full items-center gap-4">
                    <native:column :class="'w-[84] h-[84] rounded-[26] items-center justify-center bg-[' . $letterOfTheDay['category']->cardColor() . ']'">
                        <native:text :font="$letterOfTheDay['category']->font()" :class="'text-6xl text-[' . $letterOfTheDay['category']->color() . ']'">{{ $letterOfTheDay['character'] }}</native:text>
                    </native:column>
                    <native:column class="flex-1 gap-1">
                        <native:row class="items-center gap-1">
                            <native:icon :ios="Ios::StarFill" :android="Android::Star" :size="14" class="text-theme-accent" />
                            <native:text font="label" class="text-xs text-theme-accent">Letter of the day</native:text>
                        </native:row>
                        <native:text font="headline" class="text-3xl text-theme-on-surface" :max-lines="1">{{ $letterOfTheDay['name'] }}</native:text>
                        <native:text font="label" class="text-xs text-theme-on-surface-variant" :max-lines="1">{{ $letterOfTheDay['category']->title() }}</native:text>
                    </native:column>
                    <native:column class="w-[52] h-[52] rounded-full bg-theme-accent shadow-md items-center justify-center">
                        <native:icon :ios="Ios::SpeakerWave2Fill" :android="Android::VolumeUp" :size="26" class="text-theme-on-accent" />
                    </native:column>
                </native:row>
            </native:pressable>
        </native:column>

        <native:column class="w-full px-4 pt-6 gap-4">
            <native:row class="w-full items-center gap-2">
                <native:text font="headline" class="flex-1 text-2xl text-theme-on-background">Categories</native:text>
                <native:column class="px-3 py-1 rounded-full bg-theme-surface-variant">
                    <native:text font="label" class="text-xs text-theme-on-surface-variant">{{ count(LearningCategory::cases()) }} topics</native:text>
                </native:column>
            </native:row>

            @foreach ($categoryRows as $row)
                <native:row class="w-full gap-4">
                    @foreach ($row as $category)
                        {{-- The tinted wrapper shows below the card as a chunky lip --}}
                        <native:pressable
                            @navigate="'/learn/' . $category->value"
                            :press-scale="0.96"
                            :press-translate-y="3"
                            native:key="category-{{ $category->value }}"
                            :class="'flex-1 pb-[6] rounded-[28] bg-[' . $category->color() . ']/40'"
                        >
                            <native:column :class="'w-full px-3 pt-3 pb-3 gap-3 rounded-[28] items-center bg-[' . $category->cardColor() . ']'">
                                <native:row class="w-full justify-end">
                                    <native:column class="px-2 py-1 rounded-full bg-white/70">
                                        <native:text font="label" :class="'text-xs text-[' . $category->color() . ']'">{{ count($category->letters()) }} cards</native:text>
                                    </native:column>
                                </native:row>
                                <native:row class="items-center justify-center gap-2">
                                    @foreach (array_slice($category->letters(), 0, 3) as $position => $letter)
                                        <native:text :font="$category->font()" :class="'text-5xl text-[' . LearningCategory::tileColor($position) . ']'">{{ $letter->character }}</native:text>
                                    @endforeach
                                </native:row>
                                <native:row :class="'w-full px-3 py-2 gap-2 rounded-[18] items-center bg-[' . $category->color() . ']'">
                                    <native:column class="w-[30] h-[30] rounded-full bg-white/20 items-center justify-center">
                                        <native:icon :ios="Ios::BookFill" :android="Android::MenuBook" :size="18" color="#FFFFFF" />
                                    </native:column>
                                    <native:column class="flex-1">
                                        <native:text font="headline" class="text-base text-white" :max-lines="1">{{ $category->title() }}</native:text>
                                        <native:text font="label" class="text-xs text-white/80" :max-lines="1">{{ $category->subtitle() }}</native:text>
                                    </native:column>
                                </native:row>
                            </native:column>
                        </native:pressable>
                    @endforeach
                </native:row>
            @endforeach

            <native:pressable @navigate='/quiz' :press-scale="0.97" class="relative w-full p-4 rounded-[28] shadow-lg bg-gradient-to-r from-theme-quiz to-theme-quiz-light">
                <native:circle class="absolute top-[8] right-[70] w-[90] h-[90] bg-white/10" />

                <native:row class="w-full items-center gap-4">
                    <native:column class="w-[60] h-[60] rounded-[20] bg-theme-highlight items-center justify-center">
                        <native:text font="headline" class="text-4xl text-theme-on-highlight">?</native:text>
                    </native:column>
                    <native:column class="flex-1 gap-1">
                        <native:text font="headline" class="text-2xl text-theme-on-quiz">Quiz Time</native:text>
                        <native:text font="label" class="text-xs text-theme-quiz-muted">Test what you learned · all categories</native:text>
                    </native:column>
                    <native:row class="px-4 py-2 gap-1 rounded-full bg-theme-highlight items-center">
                        <native:icon :ios="Ios::PlayFill" :android="Android::PlayArrow" :size="18" class="text-theme-on-highlight" />
                        <native:text font="headline" class="text-sm text-theme-on-highlight">Play</native:text>
                    </native:row>
                </native:row>
            </native:pressable>

            <native:row class="w-full px-4 py-3 gap-3 rounded-[22] bg-theme-surface items-center">
                <native:column class="w-[40] h-[40] rounded-full bg-theme-accent/20 items-center justify-center">
                    <native:icon :ios="Ios::LightbulbFill" :android="Android::Lightbulb" :size="22" class="text-theme-accent" />
                </native:column>
                <native:text font="label" class="flex-1 text-sm text-theme-on-surface-variant">Tap a letter to hear it, then say it out loud together.</native:text>
            </native:row>
        </native:column>
    </native:column>
</native:scroll-view>
