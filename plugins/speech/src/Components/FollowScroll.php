<?php

namespace Nativephp\Speech\Components;

use Native\Mobile\Edge\Components\Native\NativeBladeComponent;

/**
 * The <native:speech-follow-scroll> tag.
 */
class FollowScroll extends NativeBladeComponent
{
    protected function elementType(): string
    {
        return 'speech_follow_scroll';
    }
}
