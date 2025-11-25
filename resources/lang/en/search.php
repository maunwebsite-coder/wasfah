<?php

return [
    'meta_title' => 'Peahskill Search',
    'badge' => 'Peahskill search',
    'heading' => [
        'results_prefix' => 'Results for',
        'results_description' => 'Refine the keyword or switch the type to surface the most relevant guides and experiences.',
        'empty_title' => 'Find a Peahskill guide or experience',
        'empty_description' => 'Search across guides, workshops, and tools crafted by Peahskill experts.',
    ],
    'form' => [
        'placeholder' => 'Search guides, workshops, or tools...',
    ],
    'types' => [
        'all' => 'All',
        'recipes' => 'Guides',
        'workshops' => 'Experiences',
    ],
    'stats' => [
        'total' => [
            'label' => 'Total matches',
            'subtitle' => 'What we found overall',
        ],
        'recipes' => [
            'label' => 'Guides',
            'subtitle' => 'Guides that match',
        ],
        'workshops' => [
            'label' => 'Experiences',
            'subtitle' => 'Live and on-demand sessions',
        ],
    ],
    'recipes' => [
        'title' => 'Guides',
        'summary' => [
            'found' => ':count matching guides',
            'empty' => 'No guides match right now',
        ],
        'view_all' => 'Explore all guides',
        'prep_time' => ':minutes min',
        'flex_time' => 'Flexible timing',
        'category_fallback' => 'Guide',
        'author_fallback' => 'Expert',
        'saved' => ':count saves',
        'view_recipe' => 'View guide',
        'empty_state' => 'We could not find matching guides. Try different keywords or pick another type.',
    ],
    'workshops' => [
        'title' => 'Experiences',
        'summary' => [
            'found' => ':count experiences available',
            'empty' => 'No experiences match right now',
        ],
        'view_all' => 'Browse all experiences',
        'featured' => 'Featured',
        'date_flexible' => 'Flexible date',
        'online' => 'Available online',
        'location_pending' => 'To be announced',
        'view_workshop' => 'View experience details',
        'empty_state' => 'We could not find matching experiences. Try other keywords or explore the available sessions.',
    ],
    'inactive' => [
        'title' => 'Start searching now',
        'description' => 'Type the name of a guide, tool, ingredient, or experience to get tailored results, or browse the main categories.',
        'chips' => [
            'French macaron',
            'Creator toolkit',
            'Beginner workshops',
            'Peahskill tools',
        ],
    ],
    'no_results' => [
        'title' => 'We could not find any results for this search',
        'description' => 'Try synonyms, reduce the keywords, or browse the main sections.',
        'home' => 'Back to home',
        'workshops' => 'Explore workshops',
    ],
];
