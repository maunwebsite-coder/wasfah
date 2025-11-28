<?php

return [
    'meta_title' => 'Record Management',

    'hero' => [
        'title' => 'Record Management',
        'description' => 'Every workshop recording available to you in one place. Watch past sessions or track booking status while you wait.',
        'explore' => 'Explore new workshops',
        'profile' => 'Back to profile',
    ],

    'stats' => [
        'total' => 'Total bookings',
        'confirmed' => 'Confirmed bookings',
        'pending' => 'Pending review',
        'updated' => 'Last updated',
    ],

    'recordings' => [
        'manage_title' => 'Record management',
        'manage_note' => 'Control which recordings appear in your view, copy their links, and understand who can access them.',
        'hide' => 'Hide from list',
        'show_all' => 'Show all hidden',
        'access_label' => 'Access',
        'access' => [
            'booking' => 'Only you (linked to your booking)',
            'drive' => 'Anyone with the link (Google Drive)',
        ],
        'copy_link' => 'Copy link',
        'copied' => 'Copied',
        'hidden_badge' => 'Hidden from public profile',
        'show_global' => 'Show on public profile',
        'hide_global' => 'Hide from public profile',
        'hide_platform' => 'Hide across platform',
        'show_platform' => 'Show across platform',
        'viewers' => 'Can watch: :count confirmed',
        'viewer_list' => 'Recent viewers',
        'hidden_success' => 'Recording hidden from public profile.',
        'shown_success' => 'Recording shown on public profile.',
    ],

    'history' => [
        'title' => 'Booking history',
        'description' => 'Bookings are sorted from newest to oldest. Use the details button to review everything or manage the booking.',
        'pagination' => 'Showing :from - :to of :total',
        'labels' => [
            'workshop' => 'Workshop',
            'untitled' => 'Workshop without a title',
            'booking_id' => 'Booking ID: :id',
            'date' => 'Date',
            'format' => 'Format',
            'online' => 'Online',
            'in_person' => 'In person',
        ],
        'actions' => [
            'details' => 'Details',
            'enter_room' => 'Enter workshop room',
            'cancel' => 'Cancel booking',
            'cancel_confirm' => 'Are you sure you want to cancel this booking? The Wasfah team will be notified automatically.',
            'watch_recording' => 'Watch recording',
        ],
        'empty' => [
            'message' => 'You have not booked a workshop yet. Explore the available sessions and reserve your first seat.',
            'cta' => 'Browse workshops now',
        ],
    ],

    'join' => [
        'meta_title' => 'Join room - :workshop',
        'header' => [
            'label' => 'Google Meet session',
            'description' => 'Thanks for joining the :workshop workshop. We use Google Meet and the link opens in a new window once the host is ready.',
        ],
        'details' => [
            'date_time' => 'Date & time',
            'soon' => 'Soon',
            'host_name' => 'Host name',
            'default_host' => 'Wasfah team',
            'meeting_status' => 'Meeting status',
        ],
        'secure' => [
            'label' => 'Secure access',
            'title' => 'Private link protected',
            'description' => 'The Google Meet link stays hidden for your security. Use the button below when the host unlocks the room.',
        ],
        'actions' => [
            'join' => 'Join via Google Meet',
            'refresh' => 'Refresh status',
        ],
        'status' => [
            'badges' => [
                'locked' => 'Room locked',
                'ready' => 'Meeting started',
                'pending' => 'Waiting for the host',
            ],
            'messages' => [
                'locked' => 'The meeting was locked by the host. Contact our support team if you need help.',
                'pending' => 'The host is preparing the room. Stay connected and refresh the status shortly.',
                'ready' => 'All set! Click the join button to open Google Meet in a new window.',
            ],
        ],
        'tips' => [
            'title' => 'Before you join',
            'items' => [
                'signin' => 'Make sure you are signed into your Google account in this browser.',
                'gear' => 'Prepare a headset and microphone that work well.',
                'focus' => 'Stay in a quiet place and close unnecessary apps for the best experience.',
                'support' => 'Refresh the status or contact support if the meeting does not open automatically.',
            ],
        ],
    ],

    'notices' => [
        'registration_closure' => 'Registration closes automatically :minutes minutes before the workshop starts based on the host time.',
    ],

    'status' => [
        'pending' => 'Pending review',
        'confirmed' => 'Confirmed',
        'cancelled' => 'Cancelled',
    ],

    'misc' => [
        'not_available' => '—',
    ],
];
