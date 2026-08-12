<?php
/**
 * Delete / Archive Habit Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$habitId = (int)($_REQUEST['id'] ?? 0);
$action  = clean_input($_REQUEST['action'] ?? 'archive');

if ($habitId <= 0) {
    set_flash_message('error', 'Invalid habit specified.');
    redirect('/habit_tracker/user/habits.php');
}

try {
    $pdo = getDBConnection();

    // STRICT OWNERSHIP CHECK: Ensure habit exists and belongs to current logged-in user
    if (!verify_habit_ownership($pdo, $habitId, $userId)) {
        set_flash_message('error', 'You are not authorized to perform this action.');
        redirect('/habit_tracker/user/habits.php');
    }

    if ($action === 'delete') {
        // Permanent deletion (Cascade deletes completion records via FK constraint)
        $stmt = $pdo->prepare("DELETE FROM habits WHERE id = :habit_id AND user_id = :user_id");
        $stmt->execute([
            'habit_id' => $habitId,
            'user_id'  => $userId
        ]);
        set_flash_message('success', 'Habit deleted permanently.');
    } else {
        // Soft-delete / Archive habit
        $stmt = $pdo->prepare("UPDATE habits SET status = 'archived' WHERE id = :habit_id AND user_id = :user_id");
        $stmt->execute([
            'habit_id' => $habitId,
            'user_id'  => $userId
        ]);
        set_flash_message('success', 'Habit archived successfully.');
    }

    redirect('/habit_tracker/user/habits.php');

} catch (PDOException $e) {
    set_flash_message('error', 'An error occurred while processing the habit request.');
    redirect('/habit_tracker/user/habits.php');
}
