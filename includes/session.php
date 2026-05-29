<?php
/**
 * session.php – Session initialisation and CSRF token management.
 * Starts a secure session with strict cookie parameters and ensures a CSRF token exists.
 */
function generate_random_token() {
    return bin2hex(openssl_random_pseudo_bytes(32));
}

// Start session only if not already active, with secure cookie settings
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,              // Session cookie (expires when browser closes)
        'path' => '/',                // Available across the whole domain
        'domain' => '',               // Current domain only
        'secure' => false,            // Allow HTTP (set to true for HTTPS only)
        'httponly' => true,           // Prevent JavaScript access to the cookie
        'samesite' => 'Lax'           // CSRF protection (good balance)
    ]);

    session_start();
}

// Generate a CSRF token if it doesn't already exist
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = generate_random_token();
}