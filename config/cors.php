<?php

return [
    'paths' => ['api/*', 'login', 'logout', 'sanctum/csrf-cookie', 'otp/verify'], // Define routes that should allow CORS
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // You can specify a domain instead of '*'
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
    //'allowed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],

];