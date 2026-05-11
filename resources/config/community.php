<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Group Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_group_members_free'        => env('HUB_MAX_MEMBERS_FREE', 50),
        'max_group_members_premium'     => env('HUB_MAX_MEMBERS_PREMIUM', 500),
        'max_groups_per_user_free'      => env('HUB_MAX_GROUPS_FREE', 3),
        'max_groups_per_user_premium'   => env('HUB_MAX_GROUPS_PREMIUM', 25),
        'max_events_per_month_free'     => env('HUB_MAX_EVENTS_FREE', 5),
        'max_events_per_month_premium'  => env('HUB_MAX_EVENTS_PREMIUM', 100),
        'max_post_length'               => 5_000,
        'max_comment_length'            => 1_000,
        'max_message_length'            => 4_000,
        'max_attachments_per_post'      => 10,
        'max_attachments_per_message'   => 5,
        'invitation_expires_days'       => 7,
        'message_edit_window_minutes'   => 15,
        'comment_max_depth'             => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Interest Categories (seed source)
    |--------------------------------------------------------------------------
    */
    'supported_interest_categories' => [
        'Sports & Fitness'  => ['Running', 'Cycling', 'Yoga', 'Football', 'Basketball', 'Climbing', 'Swimming', 'Martial Arts'],
        'Arts & Crafts'     => ['Painting', 'Pottery', 'Sketching', 'Knitting', 'Calligraphy', 'Origami'],
        'Tech & Gaming'     => ['Programming', 'Board Games', 'Video Games', 'AI/ML', 'Open Source', 'Hardware Hacking'],
        'Music'             => ['Guitar', 'Piano', 'Singing', 'DJing', 'Production', 'Choir'],
        'Outdoors'          => ['Hiking', 'Camping', 'Birdwatching', 'Fishing', 'Stargazing'],
        'Food & Drink'      => ['Cooking', 'Baking', 'Wine Tasting', 'Coffee', 'BBQ'],
        'Photography'       => ['Street', 'Portrait', 'Wildlife', 'Astrophotography'],
        'Books & Writing'   => ['Book Club', 'Creative Writing', 'Poetry', 'Journaling'],
        'Wellness'          => ['Meditation', 'Mental Health', 'Mindfulness'],
        'Travel'            => ['Backpacking', 'Road Trips', 'Solo Travel'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Moderation
    |--------------------------------------------------------------------------
    */
    'moderation' => [
        'auto_review_threshold'      => 3,      // reports needed before auto-flag
        'shadow_ban_on_report_count' => 10,
        'banned_words'               => [],     // load from a separate config or DB in production
        'require_email_verification' => true,
        'min_account_age_to_post_hours' => 0,
        'reports_notify_admin'       => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (in seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => [
        'group_details'      => 3_600,    // 1 hour
        'group_member_count' => 300,      // 5 minutes
        'group_listing'      => 600,      // 10 minutes
        'user_groups'        => 600,
        'event_attendees'    => 300,
        'feed_personal'      => 60,       // 1 minute
        'unread_messages'    => 30,
        'notifications'      => 60,
        'top_groups'         => 1_800,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tags (for invalidation)
    |--------------------------------------------------------------------------
    */
    'cache_tags' => [
        'groups'        => 'groups',
        'events'        => 'events',
        'feed'          => 'feed',
        'users'         => 'users',
        'notifications' => 'notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reverb / Broadcasting
    |--------------------------------------------------------------------------
    */
    'realtime' => [
        'typing_debounce_ms' => 1500,
        'presence_heartbeat' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */
    'features' => [
        'social_login'    => env('HUB_FEATURE_SOCIAL', true),
        'two_factor_auth' => env('HUB_FEATURE_2FA', false),
        'paid_groups'     => env('HUB_FEATURE_PAID', false),
        'live_events'     => env('HUB_FEATURE_LIVE', true),
        'mobile_push'     => env('HUB_FEATURE_PUSH', false),
    ],
];