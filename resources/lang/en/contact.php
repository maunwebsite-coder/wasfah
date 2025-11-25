<?php

return [
    'meta' => [
        'title' => 'Contact us – Peahskill',
    ],

    'hero' => [
        'badge' => 'Corporate partnerships',
        'title' => 'We are happy to hear from you',
        'description' => 'The Peahskill team supports everything related to guides, live experiences, tools, and brand collaborations. Share your message and we will reply within one business day.',
        'chips' => [
            'Workshops & training',
            'Collaboration requests',
            'Technical support',
        ],
        'stats' => [
            ['label' => 'Average response time', 'value' => '24 hours'],
            ['label' => 'Private workshops in progress', 'value' => '18+'],
            ['label' => 'Corporate partnerships', 'value' => '30'],
        ],
    ],

    'form' => [
        'title' => 'Tell us how we can help',
        'description' => 'Fill in the following details so your message reaches the right team. We normally reply within one business day.',
        'badge' => 'We review the inbox twice a day',
        'response_notice' => 'We usually reply within one business day.',
        'success' => 'Your message has been logged and the Peahskill team will get back to you shortly.',
        'submit' => 'Send message',
        'fields' => [
            'first_name' => [
                'label' => 'First name',
                'placeholder' => 'Enter your first name',
            ],
            'last_name' => [
                'label' => 'Last name',
                'placeholder' => 'Enter your last name',
            ],
            'email' => [
                'label' => 'Email',
                'placeholder' => 'example@email.com',
            ],
            'phone' => [
                'label' => 'Phone number (optional)',
                'placeholder' => 'Add a phone number so we can reach you (optional)',
            ],
            'subject' => [
                'label' => 'Subject',
            ],
            'message' => [
                'label' => 'Message',
                'placeholder' => 'Write your message or request in detail.',
            ],
        ],
        'subjects' => [
            'general' => 'General inquiry',
            'partnership' => 'Partnership or collaboration',
            'recipe' => 'Guide feedback or issue',
            'workshop' => 'Experience or session question',
            'technical' => 'Technical issue',
            'suggestion' => 'Suggestion',
            'other' => 'Other',
        ],
        'validation' => [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please select a subject.',
            'subject.in' => 'Choose a valid subject from the list.',
            'message.required' => 'Message is required.',
            'message.max' => 'Message is too long (2000 characters max).',
        ],
    ],

    'guidance_cards' => [
        [
            'title' => 'Experiences & training',
            'description' => 'Private session requests go straight to our training coordinator.',
        ],
        [
            'title' => 'Collaboration requests',
            'description' => 'We prepare a tailored proposal that matches your brand identity.',
        ],
        [
            'title' => 'Technical support',
            'description' => 'Tell us which page produced an error so we can send you the fix.',
        ],
    ],

    'faq' => [
        'title' => 'Frequently asked questions',
        'items' => [
            [
                'question' => 'When will I receive a reply?',
                'answer' => 'We check the inbox twice per day and share an initial response within one business day.',
            ],
            [
                'question' => 'Can I request a private experience or collaboration?',
                'answer' => 'Absolutely. Describe the collaboration or session you have in mind and we will coordinate with the relevant team before replying with details.',
            ],
            [
                'question' => 'What should I do if I face a technical problem?',
                'answer' => 'Let us know which page produced the issue and the steps that led to it, and we will send a fix or arrange a quick support session if needed.',
            ],
        ],
    ],

    'contact_info' => [
        'title' => 'Contact information',
        'description' => 'Pick the channel that suits you best and the Peahskill team will follow up carefully.',
        'items' => [
            [
                'title' => 'Address',
                'description' => 'Amman, Jordan',
            ],
            [
                'title' => 'Support team',
                'description' => 'We review messages twice a day during business days to deliver fast, clear responses.',
            ],
            [
                'title' => 'Communication channels',
                'description' => 'Send us a direct message on Instagram or submit the form to reach the right team.',
            ],
            [
                'title' => 'Message center',
                'description' => 'All requests are tracked through the contact form to guarantee dedicated follow-up.',
            ],
        ],
    ],

    'social' => [
        'title' => 'Follow us',
        'description' => 'Get the latest guides, experience alerts, and behind-the-scenes footage on Peahskill social channels.',
        'channels' => [
            [
                'title' => 'Instagram',
                'handle' => '@peahskill',
                'url' => 'https://www.instagram.com/peahskill/',
            ],
        ],
    ],

    'promo' => [
        'title' => 'Peahskill motto',
        'description' => 'The Peahskill platform for experts and teams walks with you every step of the way.',
        'discover' => 'Discover',
        'links' => [
            'All guides',
            'Experiences',
            'Pro tips',
            'Quick guides',
            'Tools',
            'Corporate partnerships',
            'Guide search',
            'Contact us',
        ],
        'footnotes' => [
            'Support replies within a business day when the form is used.',
            '© 2025 Peahskill. All rights reserved.',
            'We obsess over the details of every single guide.',
        ],
    ],
];
