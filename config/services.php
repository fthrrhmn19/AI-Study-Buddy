<?php

return [
    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    'you' => [
        'key' => env('YOU_API_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'developer' => [
        'name' => env('DEVELOPER_NAME', 'Fathur Rohman'),
    ],

    'team' => [
        'member_1' => env('TEAM_MEMBER_1', env('DEVELOPER_NAME', 'Fathur Rohman')),
        'member_2' => env('TEAM_MEMBER_2'),
    ],

    'ai_history' => [
        'collection' => env('AI_HISTORY_COLLECTION', 'ai_generations'),
        'legacy_collection' => env('AI_HISTORY_LEGACY_COLLECTION', 'ai_histories'),
    ],
];
