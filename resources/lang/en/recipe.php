<?php

return [
    'misc' => [
        'author_fallback' => 'Peahskill team',
        'image_alt' => 'Guide cover image',
        'gallery_image_alt' => 'Image :number',
        'placeholder_image_alt' => 'Placeholder image',
    ],

    'hero' => [
        'badge' => 'Peahskill signature guide',
        'registration_closed' => 'The booking window for this experience has ended.',
        'stats' => [
            'rating' => [
                'label' => 'Overall rating',
                'sub' => 'Community reviews',
                'empty' => 'No ratings yet',
            ],
            'saved' => [
                'label' => 'Members who saved it',
                'value' => '{0} No one saved this guide yet|{1} :count member saved this guide|[2,*] :count members saved this guide',
            ],
            'time' => [
                'total' => 'Total time',
                'servings' => 'Capacity / output',
                'details' => [
                    'prep_and_cook' => 'Prep :prep â€¢ Execute :cook',
                    'prep_only' => 'Prep :minutes',
                    'cook_only' => 'Execute :minutes',
                ],
            ],
            'servings' => [
                'value' => '{1} Delivers :count unit|[2,*] Delivers :count units',
            ],
            'published_by' => 'Published by',
            'updated_at' => 'Last updated :date',
        ],
        'byline' => [
            'chef' => 'Expert :name',
            'team' => 'Peahskill team',
        ],
        'actions' => [
            'save' => 'Save',
            'saved' => 'Saved',
            'rate' => 'Rate',
            'rated' => 'Rated',
        ],
        'buttons' => [
            'print' => 'Print',
            'share' => 'Share',
        ],
    ],

    'sections' => [
        'info' => 'Key information',
        'ingredients' => 'What you need',
        'tools' => 'Tools & resources',
        'instructions' => 'Execution steps',
        'community' => 'Community pulse',
        'share' => 'Share this guide',
        'rating' => 'Rate this guide',
        'related' => 'Similar guides from the same category',
    ],

    'info' => [
        'prep' => 'Prep time',
        'cook' => 'Execution time',
        'servings' => 'Output',
    ],

    'ingredients' => [
        'title' => 'What you need',
        'original_yield' => 'The original guide (1x) yields :count units',
        'tooltip' => 'This guide was designed with its original output. Quantities scale automatically, but timings and steps stay the same. Not every guide scales perfectly.',
        'quantity_as_needed' => 'As needed',
    ],

    'tools' => [
        'title' => 'Tools & resources',
        'price_label' => 'JOD :price',
        'price_unknown' => 'Not provided',
        'empty' => 'No tools were specified for this guide.',
        'actions' => [
            'save' => 'Save for later',
            'saving' => 'Saving...',
            'saved' => 'Saved',
            'removing' => 'Removing...',
            'error' => 'Error saving',
            'view' => 'View on Amazon',
        ],
        'messages' => [
            'save_success' => 'Tool saved for later!',
            'save_error' => 'We couldnâ€™t save this tool. Please try again.',
            'remove_success' => 'Tool removed from saved items!',
            'remove_error' => 'We couldnâ€™t remove this tool. Please try again.',
        ],
    ],

    'instructions' => [
        'title' => 'Execution steps',
    ],

    'community' => [
        'title' => 'Community pulse',
        'question' => 'Did you try this guide?',
        'count' => '{0} Be the first to try this guide! ðŸš€|{1} :count member tried this guide!|[2,*] :count members tried this guide!',
        'button' => [
            'default' => 'I tried it',
            'active' => 'Tried',
        ],
    ],

    'share' => [
        'section_title' => 'Share this guide',
        'modal' => [
            'title' => 'Share this guide',
            'options_title' => 'Choose how you want to share:',
            'copy_link' => 'Copy link',
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'copied' => 'Link copied successfully!',
            'stats' => [
                'prep' => ':minutes prep',
                'servings' => ':count servings',
            ],
        ],
    ],

    'rating' => [
        'title' => 'Rate this recipe',
        'user_rating' => 'Your rating: :rating stars',
        'prompt' => 'Please rate the recipe',
        'button' => [
            'submit' => 'Submit rating',
            'remove' => 'Remove rating',
            'rate' => 'Rate',
            'rated' => 'Rated',
        ],
        'cta' => [
            'login_link' => 'Sign in to rate this recipe',
            'login_button' => 'Sign in to rate',
        ],
        'state' => [
            'submitting' => 'Submitting...',
            'submitted' => 'Submitted',
            'removing' => 'Removing...',
        ],
        'messages' => [
            'choose_rating' => 'Please select a rating before submitting.',
            'login_required' => 'You need to sign in to rate this recipe.',
            'login_required_remove' => 'You need to sign in to remove the rating.',
            'submit_success' => 'Rating submitted successfully!',
            'submit_error' => 'We couldnâ€™t submit the rating. Please try again.',
            'remove_success' => 'Rating removed successfully!',
            'remove_error' => 'We couldnâ€™t remove the rating. Please try again.',
        ],
        'empty_state' => 'Please rate the recipe',
        'star_title' => '{1} :count star|[2,*] :count stars',
        'modal' => [
            'title' => 'Cancel rating',
            'question' => 'Are you sure you want to remove the rating?',
            'hint' => 'Your save or â€œI made itâ€ status will stay.',
            'cancel' => 'Cancel',
            'confirm' => 'Yes, remove rating',
        ],
    ],

    'related' => [
        'title' => 'Similar recipes from the same category',
        'no_ratings' => 'No ratings yet',
        'saved' => '{0} No saves yet|{1} :count save|[2,*] :count saves',
        'empty' => 'No similar recipes were found in this category.',
    ],

    'units' => [
        'minutes' => '{0} 0 minutes|{1} :count minute|[2,*] :count minutes',
        'minutes_short' => '{0} 0 min|{1} :count min|[2,*] :count min',
        'people' => '{0} 0 people|{1} :count person|[2,*] :count people',
        'servings' => '{0} Serves nobody|{1} Serves :count person|[2,*] Serves :count people',
    ],

    'actions' => [
        'print' => 'Print',
        'share' => 'Share',
        'view_recipe' => 'View recipe',
    ],
];

