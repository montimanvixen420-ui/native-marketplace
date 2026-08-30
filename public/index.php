<?php

// Baseline browser protections. Keep HSTS at the web-server level once HTTPS is enabled.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(self), microphone=()');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
}

require_once __DIR__ . '/../core/Router.php';

$router = new Router();

require __DIR__ . '/../routes/web.php';

$router->resolve();
