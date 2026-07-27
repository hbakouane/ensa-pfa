<?php

return [

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 4096),
        'temperature' => env('OPENAI_TEMPERATURE', 0.3),
        'timeout' => env('OPENAI_TIMEOUT', 60),
        'retries' => env('OPENAI_RETRIES', 3),
        'retry_delay_ms' => env('OPENAI_RETRY_DELAY_MS', 1000),
    ],

    'resume_parsing' => [
        'max_file_size_kb' => 5120, // 5MB
        'allowed_extensions' => ['pdf', 'doc', 'docx'],
    ],

    'scoring' => [
        'weights' => [
            'skills_match' => 0.35,
            'experience_match' => 0.25,
            'education_match' => 0.20,
            'overall_fit' => 0.20,
        ],
    ],

    'rate_limits' => [
        'requests_per_minute' => env('AI_RATE_LIMIT_RPM', 50),
        'tokens_per_minute' => env('AI_RATE_LIMIT_TPM', 150000),
    ],

    'queue' => env('AI_QUEUE', 'ai'),
];
