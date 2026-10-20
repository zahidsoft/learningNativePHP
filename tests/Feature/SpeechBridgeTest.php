<?php

use Native\Mobile\Testing\Native;
use Nativephp\Speech\Facades\Speech;

test('it reads the playing flag the device bridge answers with', function (array $answer, bool $isPlaying) {
    Native::fakeBridge()->respondTo('Speech.PlayFile', $answer);

    expect(Speech::playFile('/clip.mp3'))->toBe($isPlaying);
})->with([
    'as the device sends it' => [['playing' => true], true],
    'wrapped in data' => [['data' => ['playing' => true]], true],
    'not started' => [['playing' => false], false],
    'an error' => [['error' => 'FILE_NOT_FOUND'], false],
]);

test('it reads the pause and resume flags the device bridge answers with', function () {
    Native::fakeBridge()
        ->respondTo('Speech.Pause', ['paused' => true])
        ->respondTo('Speech.Resume', ['playing' => true]);

    expect(Speech::pause())->toBeTrue()
        ->and(Speech::resume())->toBeTrue();
});
