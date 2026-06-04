<?php

return [
    'default' => env('DB_CONNECTION', 'mongodb'),

    'connections' => [
        'mongodb' => [
            'driver' => 'mongodb',
            'dsn' => env('MONGODB_URI', 'mongodb://localhost:27017'),
            'database' => env('MONGODB_DATABASE', 'ai_study_buddy'),
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
];
