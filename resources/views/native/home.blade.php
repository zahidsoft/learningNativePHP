@use('App\Enums\LearningCategory')
@use('App\Icons\Android')
@use('App\Icons\Ios')

<native:top-bar title="Learn & Grow" subtitle="Small Steps, Big Future" :background-color="theme('primary')" :text-color="theme('on-primary')" font-name="headline" />

<native:scroll-view class="w-full h-full bg-theme-background">
    <native:column class="w-full pb-8">
        <native:column class="w-full px-5 pt-2 pb-7 bg-theme-primary rounded-b-[36]">
            <native:row class="w-full items-center gap-3">
                <native:column class="w-[64] h-[64] rounded-[20] bg-theme-highlight items-center justify-center">
                    <native:icon :ios="Ios::StarFill" :android="Android::Star" :size="38" class="text-theme-primary" />
                </native:column>
                <native:column class="flex-1 gap-1">
                    <native:text font="headline" class="text-3xl text-theme-on-primary">Welcome!</native:text>
                    <native:text class="text-sm text-theme-on-primary/80">Choose a category to start learning</native:text>
                </native:column>
            </native:row>
        </native:column>

        <native:column class="w-full px-4 pt-5 gap-4">
            @foreach ($categoryRows as $row)
                <native:row class="w-full gap-4">
                    @foreach ($row as $category)
                        <native:pressable
                            @navigate="'/learn/' . $category->value"
                            :press-scale="0.96"
                            native:key="category-{{ $category->value }}"
                            :class="'flex-1 px-3 pt-4 pb-3 gap-3 rounded-3xl shadow-md items-center bg-[' . $category->cardColor() . ']'"
                        >
                            <native:row class="items-center justify-center gap-2">
                                @foreach (array_slice($category->letters(), 0, 3) as $position => $letter)
                                    <native:text :font="$category->font()" :class="'text-5xl text-[' . LearningCategory::tileColor($position) . ']'">{{ $letter->character }}</native:text>
                                @endforeach
                            </native:row>
                            <native:row :class="'w-full px-3 py-2 gap-2 rounded-2xl items-center bg-[' . $category->color() . ']'">
                                <native:column class="w-[30] h-[30] rounded-full bg-white/20 items-center justify-center">
                                    <native:icon :ios="Ios::BookFill" :android="Android::MenuBook" :size="18" color="#FFFFFF" />
                                </native:column>
                                <native:column class="flex-1">
                                    <native:text font="headline" class="text-base text-white" :max-lines="1">{{ $category->title() }}</native:text>
                                    <native:text font="label" class="text-xs text-white/80" :max-lines="1">{{ $category->subtitle() }} · {{ count($category->letters()) }} cards</native:text>
                                </native:column>
                            </native:row>
                        </native:pressable>
                    @endforeach
                </native:row>
            @endforeach

            <native:pressable @navigate='/quiz' :press-scale="0.97" class="w-full p-4 rounded-3xl bg-theme-quiz shadow-md">
                <native:row class="w-full items-center gap-4">
                    <native:column class="w-[56] h-[56] rounded-2xl bg-theme-highlight items-center justify-center">
                        <native:text font="headline" class="text-4xl text-theme-on-highlight">?</native:text>
                    </native:column>
                    <native:column class="flex-1 gap-1">
                        <native:text font="headline" class="text-2xl text-theme-on-quiz">Quiz Time</native:text>
                        <native:text font="label" class="text-xs text-theme-quiz-muted">Test what you learned · all categories</native:text>
                    </native:column>
                    <native:column class="px-4 py-2 rounded-full bg-theme-highlight">
                        <native:text font="headline" class="text-sm text-theme-on-highlight">Play</native:text>
                    </native:column>
                </native:row>
            </native:pressable>

            <native:pressable
                @navigate="'/learn/' . $letterOfTheDay['category']->value . '/' . $letterOfTheDay['index']"
                :press-scale="0.97"
                class="w-full p-4 rounded-3xl bg-theme-surface shadow-md"
            >
                <native:row class="w-full items-center gap-4">
                    <native:column :class="'w-[72] h-[72] rounded-[22] items-center justify-center bg-[' . $letterOfTheDay['category']->softColor() . ']'">
                        <native:text :font="$letterOfTheDay['category']->font()" :class="'text-5xl text-[' . $letterOfTheDay['category']->color() . ']'">{{ $letterOfTheDay['character'] }}</native:text>
                    </native:column>
                    <native:column class="flex-1 gap-1">
                        <native:text font="label" class="text-xs text-theme-on-surface-variant">Letter of the day</native:text>
                        <native:text font="headline" class="text-2xl text-theme-on-surface">{{ $letterOfTheDay['name'] }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $letterOfTheDay['category']->title() }}</native:text>
                    </native:column>
                    <native:column class="w-[44] h-[44] rounded-full bg-theme-accent items-center justify-center">
                        <native:icon :ios="Ios::SpeakerWave2Fill" :android="Android::VolumeUp" :size="24" class="text-theme-on-accent" />
                    </native:column>
                </native:row>
            </native:pressable>
        </native:column>
    </native:column>
</native:scroll-view>
