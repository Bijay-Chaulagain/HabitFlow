<?php
/**
 * Login Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/habit_tracker/login.php');
}

$identifier = clean_input($_POST['username_email'] ?? '');
$password   = $_POST['password'] ?? '';

if (empty($identifier) || empty($password)) {
    set_flash_message('error', 'Please enter your username/email and password.');
    redirect('/habit_tracker/login.php');
}

try {
    $pdo = getDBConnection();

    // Query user by username OR email using prepared statement
    $stmt = $pdo->prepare("
        SELECT id, name, username, email, password, role, status 
        FROM users 
        WHERE username = :username OR email = :email 
        LIMIT 1
    ");
    $stmt->execute([
        'username' => $identifier,
        'email'    => $identifier
    ]);
    $user = $stmt->fetch();

    // Generic error message for security (prevents user enumeration)
    $genericError = 'Invalid username/email or password.';

    if (!$user) {
        set_flash_message('error', $genericError);
        redirect('/habit_tracker/login.php');
    }

    // Check account status
    if ($user['status'] !== 'active') {
        set_flash_message('error', 'Your account is inactive. Please contact an administrator.');
        redirect('/habit_tracker/login.php');
    }

    // Verify password hash
    if (!password_verify($password, $user['password'])) {
        set_flash_message('error', $genericError);
        redirect('/habit_tracker/login.php');
    }

    // Successful login: regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    // Set session data
    $_SESSION['user_id']    = (int)$user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];

    // Role-based redirection
    if ($user['role'] === 'admin') {
        redirect('/habit_tracker/admin/dashboard.php');
    } else {
        redirect('/habit_tracker/user/dashboard.php');
    }

} catch (PDOException $e) {
    set_flash_message('error', 'An unexpected error occurred during login. Please try again.');
    redirect('/habit_tracker/login.php');
}
