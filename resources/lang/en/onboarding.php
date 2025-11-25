<?php

return [
    'page_title' => 'Activate Your Expert Account',
    'header' => [
        'title' => '3 steps to activate',
        'subtitle' => 'Contact • Social (optional) • Bio (optional).',
        'chips' => [
            'instant' => 'Instant',
            'secure' => 'Protected',
            'spotlight' => 'Visible',
        ],
        'badge_tip' => 'Activate now; add your bio anytime',
        'signed_in_as' => 'Signed in as :email',
    ],
    'steps' => [
        [
            'title' => 'Contact',
            'body' => 'Country + phone.',
        ],
        [
            'title' => 'Social (optional)',
            'body' => 'Instagram or YouTube (optional).',
        ],
        [
            'title' => 'Bio (optional)',
            'body' => 'Add or skip.',
        ],
    ],
    'checklist' => [
        'title' => 'Quick check',
        'items' => [
            'Phone reachable.',
            'Optional: add a public link.',
        ],
    ],
    'sections' => [
        'contact' => [
            'title' => 'Core contact information',
            'hint' => 'All booking updates will reach this number based on the selected country.',
            'country_label' => 'Country *',
            'country_placeholder' => 'Select your country',
            'phone_label' => 'Mobile number *',
            'phone_placeholder' => 'Example: 5XXXXXXXX',
            'phone_note' => 'We will use this number to confirm bookings and send alerts.',
            'google_email_label' => 'Google Meet email *',
            'google_email_hint' => 'Use the exact Google account that will host your sessions so Google Meet lets you in instantly.',
        ],
        'social' => [
            'title' => 'Social presence',
            'hint' => 'Optional: share Instagram or YouTube to help us verify you faster. Skip if you do not have one.',
            'instagram_label' => 'Instagram link (optional)',
            'youtube_label' => 'YouTube channel link (optional)',
            'required_error' => 'Instagram or YouTube links are optional.',
            'public_notice' => 'If you share a link, keep it public so we can review it quickly.',
        ],
        'bio' => [
            'title' => 'Bio (optional)',
            'hint' => 'Help us present you better. You can skip this step and finish it later.',
            'specialty_label' => 'Main specialty (optional)',
            'specialty_placeholder' => 'Select your specialty (can be skipped)',
            'specialty_options' => [
                'food' => 'Food & culinary arts',
                'saudi_cuisine' => 'Saudi cuisine & Khaleeji flavors',
                'middle_eastern' => 'Middle Eastern classics',
                'italian' => 'Italian pasta & sauces',
                'french_pastry' => 'French pastry & viennoiserie',
                'bread_bakery' => 'Artisan bread & bakery',
                'desserts' => 'Desserts & plated sweets',
                'coffee_beverages' => 'Coffee, tea & specialty drinks',
                'grilling' => 'Grilling, BBQ & smoking',
                'seafood' => 'Seafood & fish cookery',
                'vegan' => 'Vegan & plant-based',
                'healthy_meal_prep' => 'Healthy meal prep',
                'kids_family' => 'Family & kids-friendly cooking',
                'asian_fusion' => 'Asian fusion & street food',
                'mexican_latin' => 'Mexican & Latin flavors',
            ],
            'specialty_food' => 'Food & culinary arts',
            'description_label' => 'About you (optional)',
            'description_placeholder' => 'Add a short intro now or leave it empty.',
        ],
    ],
    'alerts' => [
        'post_submit' => 'Account activates instantly; edit your bio anytime.',
    ],
    'submit' => [
        'cta' => 'Activate my expert account',
        'time_notice' => 'Three light steps—done in seconds.',
    ],
    'navigation' => [
        'back' => 'Back',
        'next' => 'Next',
        'skip_bio' => 'Skip bio for now',
        'step_status' => 'Step :current of :total',
    ],
];
