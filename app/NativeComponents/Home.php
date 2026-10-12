<?php

namespace App\NativeComponents;

use App\Enums\LearningCategory;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Home extends NativeComponent
{
    public function render(): Element
    {
        return $this->view('home', [
            'categoryRows' => array_chunk(LearningCategory::cases(), 2),
            'letterOfTheDay' => $this->letterOfTheDay(),
        ]);
    }

    /**
     * Pick one letter per calendar day, walking through every category in turn.
     *
     * @return array{category: LearningCategory, index: int, character: string, name: string}
     */
    private function letterOfTheDay(): array
    {
        $entries = [];

        foreach (LearningCategory::cases() as $category) {
            foreach ($category->letters() as $index => $letter) {
                $entries[] = [
                    'category' => $category,
                    'index' => $index,
                    'character' => $letter->character,
                    'name' => $letter->name,
                ];
            }
        }

        return $entries[(int) now()->format('z') % count($entries)];
    }
}
