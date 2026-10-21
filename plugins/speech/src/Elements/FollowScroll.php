<?php

namespace Nativephp\Speech\Elements;

use Native\Mobile\Edge\CallbackRegistry;
use Native\Mobile\Edge\Elements\ScrollView;

/**
 * A vertical scroll view that keeps one of its direct children, such as the
 * ayah being recited, in the middle of the screen while it changes.
 *
 *     <native:speech-follow-scroll :scroll-to="$playingIndex" :follow-request="$followRequest" @followChange="followChanged">
 *
 * `scroll-to` counts direct children from zero; -1 (the default) scrolls
 * nowhere. Once the reader drags the list it stops following and calls the
 * `@followChange` method with "0"; scrolling the child back into view calls it
 * with "1". Raising `follow-request` brings the child back and follows again.
 */
class FollowScroll extends ScrollView
{
    protected string $type = 'speech_follow_scroll';

    private ?string $followChangeMethod = null;

    /**
     * @return list<string>
     */
    public static function elementEvents(): array
    {
        return ['followChange'];
    }

    public function scrollTo(int $index): static
    {
        $this->scrollProps['scroll_to'] = $index;

        return $this;
    }

    public function followRequest(int $request): static
    {
        $this->scrollProps['follow_request'] = $request;

        return $this;
    }

    public function onFollowChange(string $method): static
    {
        $this->followChangeMethod = $method;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $attrs
     */
    public function applyAttributes(array $attrs): void
    {
        $index = $attrs['scroll-to'] ?? $attrs['scrollTo'] ?? null;
        $request = $attrs['follow-request'] ?? $attrs['followRequest'] ?? null;

        $this->scrollTo(is_numeric($index) ? (int) $index : -1);
        $this->followRequest(is_numeric($request) ? (int) $request : 0);
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveProps(CallbackRegistry $registry): array
    {
        $props = parent::resolveProps($registry);

        if ($this->followChangeMethod !== null) {
            $props['on_follow_change'] = $registry->register($this->followChangeMethod);
        }

        return $props;
    }
}
