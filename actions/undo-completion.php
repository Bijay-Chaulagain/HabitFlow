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

    // STRICT OWNERSHIP CHECK + fetch the habit name
    $habitStmt = $pdo->prepare("SELECT id, name FROM habits WHERE id = :habit_id AND user_id = :user_id LIMIT 1");
    $habitStmt->execute([
        'habit_id' => $habitId,
        'user_id'  => $userId
    ]);
    $habit = $habitStmt->fetch();

    if (!$habit) {
        set_flash_message('error', 'You are not authorized to modify this habit.');
        redirect($redirectTo);
    }

    // Decrement the per-day repetition count by one
    $stmt = $pdo->prepare("
        UPDATE habit_completions
        SET count = count - 1
        WHERE habit_id = :habit_id AND completion_date = :completion_date
    ");

    $stmt->execute([
        'habit_id'        => $habitId,
        'completion_date' => $completionDate
    ]);

    // Remove the row entirely once count reaches zero (count never goes negative)
    $stmt = $pdo->prepare("
        DELETE FROM habit_completions
        WHERE habit_id = :habit_id AND completion_date = :completion_date AND count <= 0
    ");

    $stmt->execute([
        'habit_id'        => $habitId,
        'completion_date' => $completionDate
    ]);

    set_flash_message('info', 'Undid one repetition for ' . $habit['name'] . ' on ' . format_date($completionDate) . '.');
    redirect($redirectTo);

} catch (PDOException $e) {
    set_flash_message('error', 'Failed to undo habit completion.');
    redirect($redirectTo);
}
