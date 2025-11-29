<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Participant Join Link Lifetime (minutes)
    |--------------------------------------------------------------------------
    |
    | Determines how long participant join links remain valid. Links are
    | generated as signed URLs and will expire after the configured window.
    |
    */
    'participant_join_ttl' => env('WORKSHOP_LINK_PARTICIPANT_TTL', 360),

    /*
    |--------------------------------------------------------------------------
    | Participant Meeting Launch Link Lifetime (minutes)
    |--------------------------------------------------------------------------
    |
    | Determines how long the signed redirect link that opens the external
    | meeting provider remains valid.
    |
    */
    'participant_launch_ttl' => env('WORKSHOP_LINK_LAUNCH_TTL', 90),

    /*
    |--------------------------------------------------------------------------
    | Participant Status Polling Link Lifetime (minutes)
    |--------------------------------------------------------------------------
    |
    | Determines how long signed polling endpoints stay valid while the
    | participant page refreshes the workshop status.
    |
    */
    'participant_status_ttl' => env('WORKSHOP_LINK_STATUS_TTL', 90),

    /*
    |--------------------------------------------------------------------------
    | Allow Same Email Multi-Device Access
    |--------------------------------------------------------------------------
    |
    | When enabled, participants who authenticate (or re-authenticate) with the
    | same email address that owns the booking can join from multiple devices
    | without being blocked by device fingerprint checks.
    |
    */
    'allow_same_email_multi_device' => env('WORKSHOP_ALLOW_SAME_EMAIL_MULTI_DEVICE', true),

    /*
    |--------------------------------------------------------------------------
    | Allow Hosts To Override Auto-Generated Meeting Links
    |--------------------------------------------------------------------------
    |
    | When true, chefs can disable the automatic Google Meet generation and
    | provide their own meeting link. Disable this in environments where you
    | want all workshops to use the centrally managed Meet credentials.
    |
    */
    'allow_host_meeting_link_override' => env('WORKSHOP_ALLOW_HOST_MEETING_OVERRIDE', true),

    /*
    |--------------------------------------------------------------------------
    | Participant Meeting Access Grace Period (hours)
    |--------------------------------------------------------------------------
    |
    | After this many hours past the scheduled workshop end time, the join
    | controls will be disabled and launch links will be blocked.
    |
    */
    'participant_meeting_grace_hours' => env('WORKSHOP_LINK_MEETING_GRACE_HOURS', 6),
];
