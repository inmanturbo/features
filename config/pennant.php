<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This option specifies the default feature flag driver that will be used
    | by the framework. The "defined-database" driver ensures that only
    | explicitly defined features are stored and retrieved from the database.
    |
    */

    'default' => env('PENNANT_DRIVER', 'defined-database'),

    /*
    |--------------------------------------------------------------------------
    | Feature Flag Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the drivers for feature flags. You may set the
    | drivers for feature flags and their settings here. Each driver has
    | a name and you can set the settings for the driver here.
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => null,
        ],

        'defined-database' => [
            'driver' => 'defined-database',
            'connection' => null,
        ],

    ],
];
