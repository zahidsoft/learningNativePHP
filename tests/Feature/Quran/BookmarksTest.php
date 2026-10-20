<?php

use App\Models\Bookmark;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    Bookmark::query()->create(['ayah_number' => 6222, 'surah' => 112, 'ayah_in_surah' => 1, 'surah_name' => 'Al-Ikhlaas']);
});

test('it lists the saved ayahs', function () {
    Native::visit('/quran/bookmarks')
        ->assertNavTitle('বুকমার্ক')
        ->assertSee('Al-Ikhlaas')
        ->assertSee('সূরা ১১২ · আয়াত ১');
});

test('it opens a saved ayah', function () {
    Native::visit('/quran/bookmarks')
        ->tap('Al-Ikhlaas')
        ->assertNavigatedTo('/quran/surah/112/1');
});

test('it removes a saved ayah', function () {
    Native::visit('/quran/bookmarks')
        ->call('remove', Bookmark::query()->sole()->id)
        ->assertSee('এখনো কোনো আয়াত সংরক্ষণ করা হয়নি');

    expect(Bookmark::query()->count())->toBe(0);
});
