<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CORS Pre-System Hook
 *
 * Handles Cross-Origin Resource Sharing for API endpoints.
 * Allowed origins are configured in application/config/cors.php
 */
function cors_handler()
{
    // Only apply CORS to API routes
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    if (strpos($request_uri, '/api/') === false) {
        return;
    }

    // Load allowed origins from config
    $allowed_origins = [];
    $cors_config = APPPATH . 'config/cors.php';
    if (file_exists($cors_config)) {
        include($cors_config);
        if (isset($config['cors_allowed_origins'])) {
            $allowed_origins = $config['cors_allowed_origins'];
        }
    }

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    // Check if origin is allowed
    $is_allowed = false;
    foreach ($allowed_origins as $allowed) {
        if ($allowed === '*') {
            $is_allowed = true;
            break;
        }
        // Support wildcard subdomain matching: *.example.com
        if (strpos($allowed, '*') !== false) {
            $pattern = '/^' . str_replace('\*', '.*', preg_quote($allowed, '/')) . '$/';
            if (preg_match($pattern, $origin)) {
                $is_allowed = true;
                break;
            }
        } elseif ($origin === $allowed) {
            $is_allowed = true;
            break;
        }
    }

    if ($is_allowed && $origin) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Auth, Authorization, X-Guest-Session, X-Requested-With');
    header('Access-Control-Max-Age: 86400');

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
