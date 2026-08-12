<?php
/**
 * Authentication and Authorization Helper Functions
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the currently logged-in user array
 */
function get_logged_in_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id'       => $_SESSION['user_id'],
        'name'     => $_SESSION['user_name'] ?? '',
        'username' => $_SESSION['username'] ?? '',
        'email'    => $_SESSION['user_email'] ?? '',
        'role'     => $_SESSION['user_role'] ?? 'user',
    ];
}

/**
 * Require user to be logged in. Redirect to login page if unauthenticated.
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash_message('error', 'Please log in to access this page.');
        redirect('/habit_tracker/login.php');
    }
}

/**
 * Require specific user role ('user' or 'admin').
 * Enforces authorization on protected pages.
 */
function require_role(string $requiredRole): void {
    require_login();

    $currentUser = get_logged_in_user();
    if ($currentUser['role'] !== $requiredRole) {
        set_flash_message('error', 'You are not authorized to perform this action or view this page.');
        
        if ($currentUser['role'] === 'admin') {
            redirect('/habit_tracker/admin/dashboard.php');
        } else {
            redirect('/habit_tracker/user/dashboard.php');
        }
    }
}

/**
 * Require 'user' role
 */
function require_user(): void {
    require_role('user');
}

/**
 * Require 'admin' role
 */
function require_admin(): void {
    require_role('admin');
}

/**
 * Verify that a habit belongs to the logged-in user
 */
function verify_habit_ownership(PDO $pdo, int $habitId, int $userId): bool {
    $stmt = $pdo->prepare("SELECT id FROM habits WHERE id = :habit_id AND user_id = :user_id");
    $stmt->execute([
        'habit_id' => $habitId,
        'user_id'  => $userId
    ]);
    return $stmt->fetch() !== false;
}
