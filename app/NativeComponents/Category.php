<?php

namespace App\NativeComponents;

use App\Enums\LearningCategory;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Category extends NativeComponent
{
    /**
     * Tiles per row: fewer, larger tiles keep short categories from looking empty.
     */
    private const WIDE_TILE_LIMIT = 12;

    public string $category = LearningCategory::English->value;

    public function mount(): void
    {
        $this->category = (LearningCategory::tryFrom((string) $this->param('category')) ?? LearningCategory::English)->value;
    }

    public function render(): Element
    {
        $category = LearningCategory::from($this->category);
        $letters = $category->letters();
        $columns = count($letters) <= self::WIDE_TILE_LIMIT ? 3 : 4;

        return $this->view('category', [
            'category' => $category,
            'letterCount' => count($letters),
            'columns' => $columns,
            'tileRows' => array_chunk($letters, $columns, preserve_keys: true),
        ]);
    }
}
