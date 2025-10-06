<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Uptime Kuma Connection
    |--------------------------------------------------------------------------
    |
    | Configure how the package connects to your Uptime Kuma instance. The
    | `base_url` should point to the root of your installation (without the
    | trailing slash). Credentials are optional when the server is running with
    | authentication disabled.
    |
    */

    'base_url' => env('UPTIME_KUMA_URL', 'http://127.0.0.1:3001'),

    'username' => env('UPTIME_KUMA_USERNAME'),

    'password' => env('UPTIME_KUMA_PASSWORD'),

    'two_factor_token' => env('UPTIME_KUMA_TOKEN'),
];
