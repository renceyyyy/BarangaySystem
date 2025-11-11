<?php
// Force HTTPS for cookies in production
ini_set('session.cookie_secure', 1);

// HttpOnly cookies
ini_set('session.cookie_httponly', 1);

// Use cookies to store session ID
ini_set('session.use_only_cookies', 1);

// Extend session lifetime
ini_set('session.gc_maxlifetime', 28800); // 8 hours
ini_set('session.cookie_lifetime', 28800); // 8 hours

// Set strict session mode
ini_set('session.use_strict_mode', 1);

// Set session name
session_name('BarangaySystemSession');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically to prevent fixation
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] >= 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
?>