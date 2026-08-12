<?php
/**
 * Admin User Status Management Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$currentAdmin = get_logged_in_user();
$currentAdminId = $currentAdmin['id'];

$targetUserId = (int)($_REQUEST['id'] ?? 0);
$action       = clean_input($_REQUEST['action'] ?? '');

if ($targetUserId <= 0) {
    set_flash_message('error', 'Invalid user account specified.');
    redirect('/habit_tracker/admin/users.php');
}

// SELF-PROTECTION GUARD: Do not allow administrators to deactivate or delete their own account!
if ($targetUserId === $currentAdminId) {
    set_flash_message('error', 'Security Protection: You cannot deactivate or delete your own active admin account.');
    redirect('/habit_tracker/admin/users.php');
}

try {
    $pdo = getDBConnection();

    // Verify target user exists
    $userStmt = $pdo->prepare("SELECT id, name, role FROM users WHERE id = :id");
    $userStmt->execute(['id' => $targetUserId]);
    $targetUser = $userStmt->fetch();

    if (!$targetUser) {
        set_flash_message('error', 'User account not found.');
        redirect('/habit_tracker/admin/users.php');
    }

    if ($action === 'activate') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = :id");
        $stmt->execute(['id' => $targetUserId]);
        set_flash_message('success', 'User account "' . e($targetUser['name']) . '" activated successfully.');
    } else if ($action === 'deactivate') {
        $stmt = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = :id");
        $stmt->execute(['id' => $targetUserId]);
        set_flash_message('warning', 'User account "' . e($targetUser['name']) . '" deactivated.');
    } else if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $targetUserId]);
        set_flash_message('success', 'User account deleted permanently.');
    } else {
        set_flash_message('error', 'Invalid action specified.');
    }

    redirect('/habit_tracker/admin/users.php');

} catch (PDOException $e) {
    set_flash_message('error', 'Failed to update user account status.');
    redirect('/habit_tracker/admin/users.php');
}
