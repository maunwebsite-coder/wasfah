<?php

return [
    'meta_title' => 'My bookings - Wasfah',

    'hero' => [
        'title' => 'My bookings',
        'description' => 'Every workshop you booked on Wasfah in one place. Track each status and enter the live room once it is confirmed.',
        'explore' => 'Explore new workshops',
        'profile' => 'Back to profile',
    ],

    'stats' => [
        'total' => 'Total bookings',
        'confirmed' => 'Confirmed bookings',
        'pending' => 'Pending review',
        'updated' => 'Last updated',
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
