<?php
/**
 * Initialize session with role-based session name
 * $roleHint: Optional role hint to use specific session name
 *            If not provided, uses default session name
 */
function initRoleBasedSession($roleHint = null) {
    // Determine the appropriate session name FIRST
    $targetSessionName = 'BarangaySystemSession'; // Default
    
    if ($roleHint === 'admin' || $roleHint === 'finance' || $roleHint === 'sk' || $roleHint === 'SuperAdmin') {
        $targetSessionName = 'BarangayStaffSession';
    } elseif ($roleHint === 'resident') {
        $targetSessionName = 'BarangayResidentSession';
    } elseif ($roleHint === null) {
        // No hint - check if we have a cookie to determine which session to use
        if (isset($_COOKIE['BarangayStaffSession'])) {
            $targetSessionName = 'BarangayStaffSession';
        } elseif (isset($_COOKIE['BarangayResidentSession'])) {
            $targetSessionName = 'BarangayResidentSession';
        }
    }
    
    // If session is already active with correct name, do nothing
    if (session_status() === PHP_SESSION_ACTIVE && session_name() === $targetSessionName) {
        return;
    }
    
    // Configure session settings BEFORE starting
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.gc_maxlifetime', 28800);
    ini_set('session.cookie_lifetime', 28800);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_secure', 0);
    
    // Set the session name
    session_name($targetSessionName);
    
    // Start the session ONLY if not active
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Regenerate session ID periodically to prevent fixation
    if (!isset($_SESSION['last_regeneration']) || 
        time() - $_SESSION['last_regeneration'] >= 1800) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// DO NOT auto-initialize - let pages call the function explicitly
// This prevents double initialization and "headers already sent" errors
?>