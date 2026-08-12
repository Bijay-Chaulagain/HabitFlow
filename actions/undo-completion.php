<?php
/**
 * Undo Habit Completion Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$habitId        = (int)($_REQUEST['habit_id'] ?? 0);
$completionDate = clean_input($_REQUEST['date'] ?? get_today_date());
$redirectTo     = $_REQUEST['redirect'] ?? '/habit_tracker/user/dashboard.php';

if (strpos($redirectTo, '/habit_tracker/') !== 0) {
    $redirectTo = '/habit_tracker/user/dashboard.php';
}

if ($habitId <= 0) {
    set_flash_message('error', 'Invalid habit specified.');
    redirect($redirectTo);
}

try {
    $pdo = getDBConnection();

    // STRICT OWNERSHIP CHECK: Ensure habit exists and belongs to current logged-in user
    if (!verify_habit_ownership($pdo, $habitId, $userId)) {
        set_flash_message('error', 'You are not authorized to modify this habit.');
        redirect($redirectTo);
    }

    // Delete completion log for date
    $stmt = $pdo->prepare("
        DELETE FROM habit_completions 
        WHERE habit_id = :habit_id AND completion_date = :completion_date
    ");

    $stmt->execute([
        'habit_id'        => $habitId,
        'completion_date' => $completionDate
    ]);

    set_flash_message('info', 'Completion undone for ' . format_date($completionDate) . '.');
    redirect($redirectTo);

} catch (PDOException $e) {
    set_flash_message('error', 'Failed to undo habit completion.');
    redirect($redirectTo);
}
