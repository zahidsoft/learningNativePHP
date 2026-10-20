<?php

namespace App\NativeComponents\Quran;

use App\Models\Bookmark;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Bookmarks extends NativeComponent
{
    public function render(): Element
    {
        return $this->view('quran.bookmarks', [
            'bookmarks' => Bookmark::query()->latest('id')->get(),
        ]);
    }

    public function remove(int $id): void
    {
        Bookmark::query()->whereKey($id)->delete();
    }
}
