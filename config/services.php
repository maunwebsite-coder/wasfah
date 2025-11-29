<?php

$defaultMeetOrganizer = 'maaunapp@gmail.com';

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'refresh_token' => env('GOOGLE_REFRESH_TOKEN', env('GOOGLE_MEET_REFRESH_TOKEN')),
    ],
    'google_meet' => [
        'client_id' => env('GOOGLE_MEET_CLIENT_ID', env('GOOGLE_CLIENT_ID')),
        'client_secret' => env('GOOGLE_MEET_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET')),
        'refresh_token' => env('GOOGLE_MEET_REFRESH_TOKEN'),
        'calendar_id' => env('GOOGLE_MEET_CALENDAR_ID', env('GOOGLE_MEET_ORGANIZER_EMAIL', $defaultMeetOrganizer)),
        'organizer_email' => env('GOOGLE_MEET_ORGANIZER_EMAIL', env('GOOGLE_MEET_CALENDAR_ID', $defaultMeetOrganizer)),
        // Default meeting duration in minutes when no env override is provided.
        'default_duration' => (int) env('GOOGLE_MEET_DEFAULT_DURATION', 45),
        'timezone' => env('GOOGLE_MEET_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
        'host_redirect_ttl' => (int) env('GOOGLE_MEET_HOST_REDIRECT_TTL', 120),
    ],
    'google_drive' => [
        'client_id' => env('GOOGLE_DRIVE_CLIENT_ID', env('GOOGLE_CLIENT_ID')),
        'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET')),
        'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
        'redirect' => env('GOOGLE_DRIVE_REDIRECT_URI', env('GOOGLE_REDIRECT_URI')),
        'service_account_json' => env('GOOGLE_SERVICE_ACCOUNT_JSON'),
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID', env('GOOGLE_RECORDINGS_FOLDER_ID')),
        'additional_folders' => env('GOOGLE_DRIVE_ADDITIONAL_FOLDER_IDS'),
        'recordings_folder_id' => env('GOOGLE_RECORDINGS_FOLDER_ID'),
        'shared_drive_id' => env('GOOGLE_DRIVE_SHARED_DRIVE_ID'),
        'max_results' => (int) env('GOOGLE_DRIVE_MAX_RESULTS', 25),
    ],

    'amazon' => [
        'affiliate_tag' => env('AMAZON_AFFILIATE_TAG', 'wasfah-21'),
        'marketplace' => env('AMAZON_MARKETPLACE', 'ae'),
    ],

    'registration' => [
        'require_email_verification' => env('REGISTRATION_REQUIRE_EMAIL_VERIFICATION'),
    ],

    'whatsapp_booking' => [
        'enabled' => (bool) env('WHATSAPP_BOOKING_ENABLED', false),
        'number' => env('WHATSAPP_BOOKING_NUMBER'),
        'notes' => env('WHATSAPP_BOOKING_NOTES', 'WhatsApp booking'),
    ],

    'stripe' => [
        'public_key' => env('STRIPE_PUBLIC_KEY'),
        'secret_key' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'payment_country' => env('STRIPE_PAYMENT_COUNTRY', 'SA'),
    ],

];
