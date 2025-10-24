<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Settings
    |--------------------------------------------------------------------------
    |
    | This file contains performance optimization settings for the MAWASA system.
    | These settings help improve the overall speed and responsiveness.
    |
    */

    'cache' => [
        // Cache duration for customer searches (in seconds)
        'customer_search_duration' => 300, // 5 minutes
        
        // Cache duration for customer account lookups (in seconds)
        'customer_account_duration' => 600, // 10 minutes
        
        // Cache duration for billing computations (in seconds)
        'billing_compute_duration' => 60, // 1 minute
        
        // Cache duration for payment history (in seconds)
        'payment_history_duration' => 300, // 5 minutes
    ],

    'database' => [
        // Enable query logging in development
        'log_queries' => env('DB_LOG_QUERIES', false),
        
        // Connection pool settings
        'pool_size' => env('DB_POOL_SIZE', 10),
    ],

    'frontend' => [
        // Debounce time for search inputs (in milliseconds)
        'search_debounce' => 150,
        
        // Local cache duration for frontend (in milliseconds)
        'local_cache_duration' => 300000, // 5 minutes
        
        // Maximum number of suggestions to show
        'max_suggestions' => 10,
    ],

    'api' => [
        // Rate limiting for API endpoints
        'rate_limit' => [
            'search' => 60, // 60 requests per minute
            'billing' => 30, // 30 requests per minute
        ],
    ],
];
