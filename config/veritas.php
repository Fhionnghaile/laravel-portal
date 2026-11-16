<?php

return [
    'driver' => env('VERITAS_DRIVER', 'fake'),   // 'fake' | 'http'
    'base_url' => env('VERITAS_BASE_URL', 'https://veritas.example.com'),
    'token' => env('VERITAS_TOKEN', ''),
    'revoked' => array_filter(explode(',', (string) env('VERITAS_REVOKED', ''))),
];
