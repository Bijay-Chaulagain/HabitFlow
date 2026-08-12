<?php
/**
 * Change Password Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/habit_tracker/user/profile.php');
}

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$currentPassword = $_POST['current_password'] ?? '';
$newPassword     = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    set_flash_message('error', 'All password fields are required.');
    redirect('/habit_tracker/user/profile.php');
}

if (!is_valid_password($newPassword, 6)) {
    set_flash_message('error', 'New password must be at least 6 characters long.');
    redirect('/habit_tracker/user/profile.php');
}

if ($newPassword !== $confirmPassword) {
    set_flash_message('error', 'New password and confirm password do not match.');
    redirect('/habit_tracker/user/profile.php');
}

try {
    $pdo = getDBConnection();

    // Fetch user password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($currentPassword, $user['password'])) {
        set_flash_message('error', 'Current password is incorrect.');
        redirect('/habit_tracker/user/profile.php');
    }

    // Hash new password using BCRYPT
    $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $updateStmt->execute([
        'password' => $newHashedPassword,
        'id'       => $userId
    ]);

    set_flash_message('success', 'Password changed successfully!');
    redirect('/habit_tracker/user/profile.php');

} catch (PDOException $e) {
    set_flash_message('error', 'An error occurred while updating password.');
    redirect('/habit_tracker/user/profile.php');
}
