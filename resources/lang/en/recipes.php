<?php

return [
    'meta' => [
        'title' => 'All Guides - Peahskill',
    ],
    'hero' => [
        'title' => 'Every guide in one showcase',
        'badge' => [
            'default' => 'Peahskill Picks',
            'category' => 'Category :category',
            'search' => 'Search: ":term"',
        ],
        'subtitle' => [
            'default' => 'Discover curated, practical guides from the Peahskill team.',
            'category' => 'Everything about :category in one place.',
            'search' => 'Showing results for ":term".',
        ],
        'meta_search' => 'Search: ":term"',
        'latest_unavailable' => 'Not available',
    ],
    'sort' => [
        'created_at' => 'Newest',
        'rating' => 'Top rated',
        'saved' => 'Most saved',
    ],
    'difficulty' => [
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
    ],
    'stats' => [
        'total' => [
            'label' => 'Total guides',
            'hint' => 'Across the Peahskill library',
        ],
        'current' => [
            'label' => 'Currently showing',
            'hint' => 'From :first to :last',
        ],
        'filters' => [
            'label' => 'Filter count',
            'hint' => [
                'active' => 'Custom settings',
                'default' => 'Default view',
            ],
        ],
        'latest' => [
            'label' => 'Latest addition',
            'hint' => 'Most recently published guide',
        ],
    ],
    'filters' => [
        'search_label' => 'Search guide',
        'search_placeholder' => 'Example: Product brief template',
        'category_label' => 'Categories',
        'sort_label' => 'Sort results',
        'all_categories' => 'All categories',
        'submit' => 'Show results',
        'reset' => 'Reset',
        'active_label' => 'Active filters:',
        'chip_search' => 'Search: ":term"',
        'chip_category' => 'Category: :category',
        'chip_sort' => 'Sort: :label',
    ],
    'cards' => [
        'category_fallback' => 'Guide',
        'fallback_excerpt' => 'Learn how to apply this guide with clear, friendly steps.',
        'prep_time' => ':minutes min',
        'servings' => '{1} Delivers one output|[2,*] Delivers :count outputs',
        'booking_closed' => 'Booking closed',
        'view_recipe' => 'View guide',
        'image_fallback_alt' => 'Fallback image',
    ],
    'pagination' => [
        'summary' => ':first - :last of :total guides',
    ],
    'empty' => [
        'title' => 'No matching results',
        'subtitle' => 'Try adjusting your search or picking a different category. We add new guides regularly.',
        'cta' => 'Show all guides',
    ],
];
