<?php
/**
 * Logout Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start a clean session for flash message
session_start();
set_flash_message('success', 'You have been logged out successfully.');

redirect('/habit_tracker/login.php');
