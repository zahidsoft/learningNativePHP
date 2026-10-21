<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\TestableComponent;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Map every node id in a published tree to its element type.
 *
 * @param  array<string, mixed>  $node
 * @param  array<string, string>  $types
 * @return array<string, string>
 */
function nodeTypes(array $node, array $types = []): array
{
    $types[(string) $node['id']] = (string) $node['type'];

    foreach ($node['children'] ?? [] as $child) {
        $types = nodeTypes($child, $types);
    }

    return $types;
}

/**
 * Run each step and fail when one draws a node id as a different element
 * than the frame before it: the Android renderer crashes on that.
 *
 * Each step returns the screen whose new frame should be checked.
 *
 * @param  list<Closure(): TestableComponent>  $steps
 */
function expectStableNodeTypes(TestableComponent $screen, array $steps): void
{
    $before = nodeTypes($screen->tree());

    foreach ($steps as $index => $step) {
        $frame = nodeTypes($step()->tree());
        $changed = array_filter(array_keys(array_intersect_key($frame, $before)), fn (string $id): bool => $frame[$id] !== $before[$id]);

        expect($changed)->toBe([], 'Step '.($index + 1).' changed the type of node(s): '.implode(', ', array_map(fn ($id) => "{$id}: {$before[$id]} → {$frame[$id]}", $changed)));

        $before = $frame;
    }
}
