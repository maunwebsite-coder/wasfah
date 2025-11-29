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
        'hub_title' => 'Google Drive control center',
        'hub_subtitle' => 'This page is the first stop when you want to control which workshops from Google Drive show on the site. Link your account, review recordings, and manage visibility.',
        'drive_status_connected' => 'Google Drive linked and ready',
        'drive_status_disconnected' => 'Link Google Drive to push or hide your recordings with one click.',
        'visible_count' => 'Visible to visitors: :count',
        'hidden_count' => 'Hidden right now: :count',
        'manage_workshops' => 'Manage my workshops',
        'refresh_drive' => 'Refresh Drive access',
        'connect_drive' => 'Connect Google Drive',
        'open_drive' => 'Open Google Drive',
        'hub_stats' => [
            'drive' => 'Drive recordings',
            'owned' => 'Workshops you own',
            'visibility' => 'Visibility control',
        ],
        'visibility_note' => 'Visible recordings are instantly shown on your public page.',
        'hidden_note' => 'Hidden recordings stay private until you show them again.',
        'drive_library_description' => 'A snapshot of the latest video files in this account\'s Google Drive.',
        'drive_library_title' => 'Google Drive library',
        'drive_focus_title' => 'Recordings ready to publish',
        'drive_focus_subtitle' => 'Pick which recordings to show and decide if they stay platform-only or public.',
        'drive_empty' => 'No Google Drive videos pulled in yet. Refresh the link or open Drive to pick your files.',
        'filters' => [
            'title' => 'Quick filters',
            'all' => 'All',
            'bookings' => 'My bookings',
            'owned' => 'Workshops I own',
            'drive' => 'From Google Drive',
            'hidden' => 'Hidden only',
            'search_placeholder' => 'Search by workshop name or keyword',
        ],
        'legend' => [
            'public' => 'Visible to visitors',
            'hidden' => 'Hidden from visitors',
            'platform' => 'Hidden across the platform',
        ],
        'card_badges' => [
            'booking' => 'Booking',
            'owner' => 'Owner workshop',
            'drive' => 'Google Drive',
        ],
        'empty_filtered' => 'No recordings match the current filters. Try switching the view or using another search term.',
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
            'hosted_by' => 'Host: :name',
            'online' => 'Online',
            'in_person' => 'In person',
        ],
        'actions' => [
            'details' => 'Details',
            'enter_room' => 'Attend workshop recording',
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
            'end_time' => 'Ends at',
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
            'rate' => 'Rate the workshop',
            'refresh' => 'Refresh status',
        ],
        'status' => [
            'badges' => [
                'expired' => 'Session closed',
                'locked' => 'Room locked',
                'ready' => 'Meeting started',
                'grace' => 'Join now',
                'pending' => 'Waiting for the host',
            ],
            'messages' => [
                'expired' => 'This workshop has ended. Joining is disabled 6 hours after the end time.',
                'locked' => 'The meeting was locked by the host. Contact our support team if you need help.',
                'pending' => 'The host is preparing the room. Stay connected and refresh the status shortly.',
                'pending_grace' => 'Waiting for the host. Joining will unlock automatically :minutes minutes after start time.',
                'ready' => 'All set! Click the join button to open Google Meet in a new window.',
                'grace' => 'You can join now because :minutes minutes have passed since the scheduled start, even if the host has not begun.',
                'grace_wait' => 'Joining will unlock automatically :minutes minutes after the scheduled start, even if the host has not begun.',
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
