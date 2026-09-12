<?php

return [
    // External AI provider configuration intentionally removed.
    // BookNest's Library Assistant is fully rule-based and local.
        'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],
];
