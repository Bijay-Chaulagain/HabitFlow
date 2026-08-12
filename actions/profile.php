<?php
/**
 * Update Profile Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/habit_tracker/user/profile.php');
}

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$name = clean_input($_POST['name'] ?? '');

if (empty($name)) {
    set_flash_message('error', 'Full name cannot be empty.');
    redirect('/habit_tracker/user/profile.php');
}

try {
    $pdo = getDBConnection();

    $stmt = $pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'id'   => $userId
    ]);

    $_SESSION['user_name'] = $name;

    set_flash_message('success', 'Profile updated successfully!');
    redirect('/habit_tracker/user/profile.php');

} catch (PDOException $e) {
    set_flash_message('error', 'Failed to update profile.');
    redirect('/habit_tracker/user/profile.php');
}
