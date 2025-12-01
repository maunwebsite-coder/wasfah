<?php

return [
    'meta' => [
        'title' => 'Workshops – Wasfah',
    ],

    'featured' => [
        'badge' => 'Upcoming workshop',
        'completed' => 'Workshop completed',
        'booked' => 'Already booked',
        'full' => 'Workshop is full',
        'closed' => 'Registration closed',
        'primary_cta' => 'Register for the workshop',
        'secondary_cta' => 'More details',
        'no_upcoming_title' => 'There are no upcoming workshops right now',
        'no_upcoming_description' => 'We are preparing new standout workshops for you. Stay tuned for the next one!',
        'no_upcoming_primary' => 'Browse all workshops',
        'no_upcoming_secondary' => 'Discover recipes',
    ],

    'filters' => [
        'all' => 'All',
        'online' => 'Online',
        'offline' => 'In person',
        'beginner' => 'Beginner',
        'advanced' => 'Advanced',
    ],

    'premium' => [
        'title' => 'Peahskill signature workshops',
        'subtitle' => 'Train with Peahskill pastry mentors in curated masterclasses that blend global techniques with hands-on practice',
    ],

    'cards' => [
        'online_badge' => 'Online',
        'onsite_badge' => 'In person',
        'overlay_full' => 'Fully booked',
        'overlay_closed' => 'Registration closed',
        'button_book' => 'Book now',
        'button_details' => 'Details',
    ],

    'empty' => [
        'title' => 'No workshops available right now',
        'description' => 'We are preparing new standout workshops. Check again soon!',
    ],

    'why' => [
        'title' => 'Why choose our workshops?',
        'items' => [
            'chefs' => [
                'title' => 'Peahskill pastry mentors',
                'description' => 'Train directly with Peahskill-certified pastry coaches using our proven methods.',
            ],
            'hands_on' => [
                'title' => 'Live, step-by-step coaching',
                'description' => 'Follow along in real time with guided checkpoints instead of prerecorded lectures.',
            ],
            'ingredients' => [
                'title' => 'Curated ingredient lists',
                'description' => 'Peahskill-approved shopping lists and swaps keep every dessert consistent.',
            ],
            'certificate' => [
                'title' => 'Peahskill completion badge',
                'description' => 'Earn a shareable certificate and unlock advanced tracks when you finish.',
            ],
        ],
    ],

    'faq' => [
        'title' => 'Frequently asked questions',
        'items' => [
            [
                'question' => 'How can I register for a workshop?',
                'answer' => 'Click the "Book now" button on the workshop card to open the workshop page on Wasfah, then sign in and finish your booking and payment securely.',
            ],
            [
                'question' => 'Do I need previous cooking experience?',
                'answer' => 'No. Most workshops are designed for beginners, and we also cover intermediate and advanced levels so you can pick what fits you.',
            ],
            [
                'question' => 'What equipment do I need for online workshops?',
                'answer' => 'You will need a computer or smartphone with a camera, a stable internet connection, and your basic kitchen tools. We send the ingredients list in advance.',
            ],
        ],
    ],

    'no_results' => [
        'title' => 'No workshops match this filter',
        'description' => 'Try another filter.',
    ],

    'labels' => [
        'online_workshop' => 'Online workshop',
        'offline_workshop' => 'In-person workshop',
        'online_short' => 'Online',
        'onsite_short' => 'In person',
        'online_live' => 'Online (live)',
        'with' => 'With',
        'participants' => 'participants',
        'unspecified' => 'Not specified',
        'featured_placeholder_text' => 'Premium workshop',
        'card_placeholder_text' => 'Workshop',
        'fallback_image_alt' => 'Placeholder image',
    ],

    'stripe' => [
        'label' => 'Card payment',
        'title' => 'Visa / Mastercard via Stripe',
        'description' => 'Enter your card details and your booking will be confirmed immediately after payment.',
        'pay_button' => 'Pay now',
        'processing' => 'Processing payment...',
        'success_message' => 'Payment captured and your booking is confirmed!',
        'generic_error' => 'Something went wrong while processing the card. Please try again.',
        'init_error' => 'Unable to load the Stripe checkout. Please refresh the page.',
        'validation_error' => 'Please double-check your card details and try again.',
        'disabled' => 'Card payments are not enabled right now.',
        'not_ready' => 'The card form is still loading. Please wait a moment.',
        'intent_error' => 'The payment session expired. Please refresh and try again.',
        'wallet_label' => 'Apple Pay / Google Pay',
        'wallet_hint' => 'Use Apple Pay or Google Pay for an instant checkout if your device supports it.',
        'wallet_unavailable' => 'Wallet payments show automatically on supported browsers and devices.',
    ],

    'flash' => [
        'updated' => 'Workshop updated successfully!',
        'deleted' => 'Workshop deleted successfully!',
    ],

    'booking_modal' => [
        'title' => 'Booking confirmation',
        'message' => 'Are you sure you want to book this workshop?',
        'fields' => [
            'date' => 'Date:',
            'start' => 'Start date:',
            'end' => 'End date:',
            'instructor' => 'Instructor:',
            'location' => 'Location:',
            'price' => 'Price:',
        ],
        'actions' => [
            'confirm' => 'Yes, book now',
            'cancel' => 'Cancel',
        ],
    ],

    'whatsapp' => [
        'button' => 'Book via WhatsApp',
        'followup_title' => 'Follow up on your WhatsApp booking',
        'helper' => 'One tap opens WhatsApp with a pre-filled message to our booking team.',
        'note' => 'Note: Booking in person adds 1 USD to the workshop price, so online reservations keep the best rate.',
        'inquiry_button' => 'Ask about my booking on WhatsApp',
        'inquiry_helper' => 'We include your booking reference so the Wasfah team can reply quicker.',
        'pending_badge' => 'Your request is awaiting Wasfah confirmation',
        'pending_helper' => 'We will notify you as soon as it is reviewed. You can also track it from “My Bookings”.',
        'terms_fallback' => "Please note that bookings follow Wasfah's terms and cancellation policy: :url",
    ],

    'whatsapp_verification' => [
        'title' => 'WhatsApp Booking Confirmation',
        'button' => 'Verify WhatsApp Booking',
        'helper' => 'Opens WhatsApp so you can follow up with the Wasfah team — and we still refresh the status for you.',
        'loading' => 'Checking...',
        'awaiting_confirmation' => 'Your WhatsApp request is logged and will be confirmed shortly.',
        'success' => 'Current status: :status · Last update :updated_at.',
        'status_pending' => 'Pending review',
        'status_confirmed' => 'Confirmed',
        'not_found' => 'We could not find a WhatsApp booking linked to your account for this workshop.',
        'login_required' => 'Please sign in to track your WhatsApp booking.',
        'unexpected_error' => 'Something went wrong while checking your booking. Please try again later.',
        'unknown_time' => 'Unknown time',
        'message_template' => 'Hi Wasfah team, I would like to verify my WhatsApp booking :booking for workshop :workshop.',
        'reference_unknown' => 'Not available',
    ],

    'reviews' => [
        'title' => 'Workshop rating',
        'rating_label' => 'Your rating',
        'comment_label' => 'Your feedback',
        'optional' => 'Optional',
        'comment_placeholder' => 'Share what you loved or what could be improved.',
        'submit' => 'Submit review',
        'update' => 'Update review',
        'messages' => [
            'wait_until_end' => 'You can add a review 15 minutes after the workshop starts.',
            'participant_only' => 'Only attendees who booked this workshop can leave a rating.',
            'login_to_review' => 'Sign in to leave a rating 15 minutes after the workshop starts.',
        ],
        'flash' => [
            'submitted' => 'Your rating has been saved. Thanks for sharing your feedback!',
        ],
        'errors' => [
            'not_finished' => 'You can only submit a rating after 15 minutes have passed from the start time.',
            'not_participant' => 'Only participants with a booking can review this workshop.',
        ],
    ],

    'details' => [
        'title_suffix' => 'Wasfah Workshop',
        'not_specified' => 'Not specified',
        'booking_status' => [
            'open' => 'Open for booking',
            'completed' => 'Workshop completed',
            'full' => 'Fully booked',
            'closed' => 'Registration closed',
            'booked' => 'Your booking is active',
        ],
        'hero' => [
            'featured_badge' => 'Featured workshop',
            'default_badge' => 'Workshop',
            'date_label' => 'DATE',
            'start_label' => 'START',
            'end_label' => 'END',
            'format_label' => 'FORMAT',
            'instructor_label' => 'INSTRUCTOR',
            'duration_label' => 'DURATION',
            'capacity_label' => 'CAPACITY',
            'rating_label' => 'RATING',
            'reviews_count' => ':count reviews',
        ],
        'sections' => [
            'about' => 'About the workshop',
            'learn' => 'What you will learn',
            'requirements' => 'Requirements',
            'materials' => 'Materials needed',
            'recipes' => 'Workshop recipes',
        ],
        'recipes' => [
            'placeholder' => 'Recipe',
            'minutes' => ':count min',
            'servings' => ':count servings',
            'view' => 'View recipe',
            'difficulty' => [
                'easy' => 'Easy',
                'medium' => 'Medium',
                'hard' => 'Advanced',
            ],
        ],
        'booking_card' => [
            'eyebrow' => 'Bookings',
            'title' => 'Reserve your seat',
            'subtitle' => 'Aligned with the Wasfah UI for a clearer booking flow.',
            'price_label' => 'Price',
            'price_hint' => 'Includes Wasfah fees and taxes',
            'date_label' => 'Workshop date',
            'join_label' => 'Join method',
            'join_hint_online' => 'The join link is sent automatically after confirmation.',
            'join_hint_offline' => 'Exact location is shared after completing payment.',
            'deadline_label' => 'Registration deadline',
            'hours_label' => 'Workshop hours',
            'instructor_label' => 'Led by',
            'confirmed_label' => 'Confirmed seats',
            'cta_completed' => 'Workshop completed',
            'cta_full' => 'Fully booked',
            'cta_closed' => 'Registration closed',
            'cta_join' => 'Join the live room',
            'join_room_hint' => 'The room unlocks inside your account when the workshop starts.',
            'cta_confirmed' => 'Booking confirmed',
            'confirmation_hint' => 'You will receive the join link as soon as it is ready.',
            'cta_pending' => 'Booking under review',
            'pending_hint' => 'We will contact you once the booking is approved. Track it from your bookings page.',
            'whatsapp_prompt' => 'Need to confirm or modify your booking on WhatsApp?',
            'whatsapp_followup_hint' => 'Our team will reach out regarding any notes tied to your booking.',
            'login_required' => 'Sign in to book the workshop',
            'login_hint' => 'Sign in to pay instantly and unlock the workshop seat.',
            'payments_disabled_title' => 'Online payments are disabled',
            'payments_disabled_hint' => 'Contact Wasfah support to enable payment gateways before taking bookings.',
            'summary' => [
                'title' => 'Workshop summary',
                'date' => 'Date',
                'start_time' => 'Start time',
                'end_time' => 'End time',
                'instructor' => 'Instructor',
                'location' => 'Location',
                'cost' => 'Cost',
            ],
        ],
        'sidebar' => [
            'title' => 'Additional details',
            'start' => 'Start date',
            'end' => 'End date',
            'category' => 'Category',
            'level' => 'Skill level',
            'views' => 'Views',
            'address' => 'Address',
            'levels' => [
                'beginner' => 'Beginner',
                'advanced' => 'Advanced',
            ],
        ],
        'related' => [
            'title' => 'You may also like',
            'badge_full' => 'Full',
            'details' => 'Details',
        ],
        'modal' => [
            'title' => 'Sign in required',
            'description' => 'You must sign in before booking this workshop.',
            'hint' => 'Sign in or create an account to continue.',
            'login' => 'Sign in',
            'register' => 'Create account',
        ],
        'timezones' => [
            'host_label' => 'Chef time',
            'viewer_label' => 'Your local time',
            'viewer_placeholder' => 'Adjusting to your timezone…',
            'viewer_timezone_template' => ':label :date (:offset · :timezone)',
            'viewer_timezone_fallback' => 'your timezone',
            'host_timezone_template' => ':label :date (:offset · :timezone)',
        ],
        'messages' => [
            'unexpected_error' => 'Something went wrong. Please try again later.',
            'payment_success' => 'Payment captured and your booking is confirmed!',
            'stripe_error' => 'Unable to complete the payment.',
            'stripe_cancelled' => 'Payment process cancelled.',
            'join_link_ready' => 'Your meeting link is ready. You can open it in a new tab when you\'re ready.',
            'join_link_action' => 'Open meeting link',
        ],
    ],
];
