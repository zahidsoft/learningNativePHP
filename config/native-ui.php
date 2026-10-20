<?php

/**
 * Native UI — Theme Tokens
 *
 * Published via `php artisan vendor:publish --tag=native-ui-config`.
 * Edit to customize your app's visual identity in one place.
 */

/*
| The learning app has one fixed, bright look for children, so the same
| palette is used for both appearances instead of letting the dark tokens be
| derived from the light ones.
*/
$palette = [
    // Primary brand color — the home header and other key accents.
    'primary' => '#1478E6',
    'on-primary' => '#FFFFFF',
    // Lighter end of the header gradient.
    'primary-light' => '#57B6FA',

    'secondary' => '#3A6390',
    'on-secondary' => '#FFFFFF',

    // Surface = cards and tiles. Background = page root.
    'surface' => '#FFFFFF',
    'on-surface' => '#173D6B',
    'background' => '#EAF4FF',
    'on-background' => '#173D6B',

    // Surface variant = muted tonal surfaces; on-surface-variant = muted labels.
    'surface-variant' => '#D6ECFF',
    'on-surface-variant' => '#4A6D94',

    'outline' => '#CBD5E1',
    'outline-variant' => '#E2E8F0',

    // Wrong quiz answers.
    'destructive' => '#EF476F',
    'on-destructive' => '#FFFFFF',

    // Right quiz answers.
    'success' => '#3EC46A',
    'on-success' => '#FFFFFF',

    // "Tap to hear" and other call-to-action buttons.
    'accent' => '#FFB400',
    'on-accent' => '#FFFFFF',

    // The quiz: its deep indigo page, and the yellow used on top of it.
    'quiz' => '#2F2B7A',
    'on-quiz' => '#FFFFFF',
    // Lighter end of the quiz banner gradient.
    'quiz-light' => '#6D4BD6',
    'quiz-muted' => '#C7C2FF',
    'highlight' => '#FFD23F',
    'on-highlight' => '#2A2F6B',

    // The spelling game: its teal header and the pale page behind the puzzle.
    'spell' => '#0E9F8E',
    'on-spell' => '#FFFFFF',
    // Lighter end of the spelling card gradient.
    'spell-light' => '#3FD1BC',
    'spell-soft' => '#E3FAF6',

    // Streak flame on the home header.
    'streak' => '#FF7A1A',
];

return [

    /*
    |---------------------------------------------------------------------------
    | Theme
    |---------------------------------------------------------------------------
    |
    | The token map is open-ended: any key added to the palette resolves
    | immediately as `bg-theme-<key>` / `text-theme-<key>` / `border-theme-<key>`,
    | and theme classes accept opacity modifiers (`bg-theme-primary/15`).
    | In PHP, read tokens with the appearance-aware `theme()` helper.
    |
    */

    'theme' => [

        'light' => $palette,

        'dark' => $palette,

        // Corner radii (points / dp).
        'radius-sm' => 4,
        'radius-md' => 8,
        'radius-lg' => 16,
        'radius-full' => 9999,

        // Font size scale (points / sp).
        'font-sm' => 14,
        'font-md' => 16,
        'font-lg' => 20,
        'font-xl' => 24,

    ],

    /*
    |---------------------------------------------------------------------------
    | Fonts
    |---------------------------------------------------------------------------
    |
    | Semantic names for the bundled fonts in resources/fonts/ (file tokens,
    | minus the extension). One file is one weight, so views pick a weight by
    | alias rather than with font-bold classes.
    |
    | Baloo Da 2 covers both Latin and Bangla, so one face draws English and
    | Bangla letters alike; Arabic letters need their own face.
    |
    */

    'fonts' => [
        'default' => 'Nunito-Bold',
        'label' => 'Nunito-ExtraBold',
        'headline' => 'BalooDa2-ExtraBold',
        'glyph' => 'BalooDa2-Bold',
        'arabic' => 'NotoNaskhArabic-Bold',
    ],

];
