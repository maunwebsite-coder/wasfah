<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master toggle
    |--------------------------------------------------------------------------
    */
    'enabled' => env('CONTENT_MODERATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Terms that should never appear in user generated fields.
    |--------------------------------------------------------------------------
    */
    'blocked_terms' => [
        // Arabic
        'قذر',
        'لعنة',
        'قحبة',
        'شرموطة',
        'سكس',
        'جنس',
        'اباحي',
        'اباحية',
        'مثير',
        'زب',
        'كس',
        'طيز',
        'قضيب',
        'مهبل',
        'شاذ',

        // English
        'fuck',
        'shit',
        'bitch',
        'slut',
        'whore',
        'pussy',
        'dick',
        'porn',
        'porno',
        'pornographic',
        'xxx',
        'nsfw',
        'nude',
        'naked',
        'sex',
        'sexy',
        'anal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Input keys that should be skipped when scanning for profanity.
    |--------------------------------------------------------------------------
    */
    'excluded_input_keys' => [
        '_token',
        '_method',
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image upload policies
    |--------------------------------------------------------------------------
    */
    'image' => [
        'max_kilobytes' => (int) env('IMAGE_UPLOAD_MAX_KB', 25600),

        'allowed_mime_types' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
        ],

        'processing' => [
            'quality' => (int) env('IMAGE_PROCESSING_QUALITY', 80),
            'max_width' => (int) env('IMAGE_PROCESSING_MAX_WIDTH', 1920),
            'max_height' => (int) env('IMAGE_PROCESSING_MAX_HEIGHT', 1920),
        ],

        'review' => [
            'enabled' => env('IMAGE_EXPLICIT_SCAN', true),
            'skin_tone_threshold' => 0.38,
            'sample_target' => 2000,
        ],
    ],
];
