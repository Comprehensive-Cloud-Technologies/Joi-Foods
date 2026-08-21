<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| CORS Allowed Origins
|--------------------------------------------------------------------------
|
| List of origins allowed to make cross-origin requests to the API.
| This applies to all /api/* routes.
|
| Examples:
|   'http://localhost'              - exact match
|   'http://localhost:3000'         - localhost with specific port
|   'https://example.com'           - production domain
|   'https://*.example.com'         - wildcard subdomain
|   '*'                             - allow all origins (NOT recommended for production)
|
*/
$config['cors_allowed_origins'] = [
    'http://localhost',
    'http://localhost:3000',
    'http://localhost:5175',
    'http://localhost:5500',
    'http://localhost:8080',
    'http://127.0.0.1',
    'http://127.0.0.1:3000',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:5500',
    // Production domains
    'https://*.zbit.ltd',
    'https://*.joifood.co',
];
