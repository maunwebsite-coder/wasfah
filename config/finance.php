<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Finance Settings
    |--------------------------------------------------------------------------
    |
    | Central place to define how workshop payments are split once a booking
    | is approved and marked as paid. Percentages are expressed as numbers
    | between 0 and 100 and will be normalized automatically to ensure
    | the final share does not exceed 100% after referral commissions.
    |
    */

    'default_currency' => strtoupper(env('FINANCE_DEFAULT_CURRENCY', 'JOD')),

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies & Metadata
    |--------------------------------------------------------------------------
    |
    | Each currency entry defines how amounts are formatted and converted
    | internally. rate_to_usd represents the value of 1 unit of the currency
    | expressed in USD.
    |
    */

    'supported_currencies' => [
        'USD' => [
            'label' => 'دولار أمريكي',
            'symbol' => '$',
            'decimals' => 2,
            'rate_to_usd' => 1.0,
        ],
        'SAR' => [
            'label' => 'ريال سعودي',
            'symbol' => 'ر.س',
            'decimals' => 2,
            'rate_to_usd' => 0.266667,
        ],
        'AED' => [
            'label' => 'درهم إماراتي',
            'symbol' => 'د.إ',
            'decimals' => 2,
            'rate_to_usd' => 0.272294,
        ],
        'KWD' => [
            'label' => 'دينار كويتي',
            'symbol' => 'د.ك',
            'decimals' => 3,
            'rate_to_usd' => 3.25,
        ],
        'BHD' => [
            'label' => 'دينار بحريني',
            'symbol' => 'د.ب',
            'decimals' => 3,
            'rate_to_usd' => 2.65,
        ],
        'QAR' => [
            'label' => 'ريال قطري',
            'symbol' => 'ر.ق',
            'decimals' => 2,
            'rate_to_usd' => 0.274725,
        ],
        'OMR' => [
            'label' => 'ريال عماني',
            'symbol' => 'ر.ع',
            'decimals' => 3,
            'rate_to_usd' => 2.60,
        ],
        'JOD' => [
            'label' => 'دينار أردني',
            'symbol' => 'د.أ',
            'decimals' => 3,
            'rate_to_usd' => 1.41,
        ],
        'EGP' => [
            'label' => 'جنيه مصري',
            'symbol' => 'ج.م',
            'decimals' => 2,
            'rate_to_usd' => 0.020,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-Based Shares
    |--------------------------------------------------------------------------
    |
    | - chef_share_percent: the target percentage that goes to the chef.
    | - admin_share_percent: fallback percentage for the platform. The actual
    |   value is recalculated after deducting referral partner commissions.
    |
    */

    'chef_share_percent' => (float) env('FINANCE_CHEF_SHARE_PERCENT', 70),

    'admin_share_percent' => (float) env('FINANCE_ADMIN_SHARE_PERCENT', 30),

    /*
    |--------------------------------------------------------------------------
    | Safety limits
    |--------------------------------------------------------------------------
    |
    | Prevents the system from generating odd splits even if environment
    | variables are misconfigured.
    |
    */

    'min_chef_percent' => 40.0,
    'max_chef_percent' => 90.0,
];
