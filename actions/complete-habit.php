<?php
/**
 * Mark Habit Complete Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$habitId        = (int)($_REQUEST['habit_id'] ?? 0);
$completionDate = clean_input($_REQUEST['date'] ?? get_today_date());
$redirectTo     = $_REQUEST['redirect'] ?? '/habit_tracker/user/dashboard.php';

// Sanitize redirect target to internal relative paths only
if (strpos($redirectTo, '/habit_tracker/') !== 0) {
    $redirectTo = '/habit_tracker/user/dashboard.php';
}

if ($habitId <= 0) {
    set_flash_message('error', 'Invalid habit specified.');
    redirect($redirectTo);
}

try {
    $pdo = getDBConnection();

    // STRICT OWNERSHIP CHECK + fetch the habit target and name
    $habitStmt = $pdo->prepare("SELECT id, name, target FROM habits WHERE id = :habit_id AND user_id = :user_id LIMIT 1");
    $habitStmt->execute([
        'habit_id' => $habitId,
        'user_id'  => $userId
    ]);
    $habit = $habitStmt->fetch();

    if (!$habit) {
        set_flash_message('error', 'You are not authorized to modify this habit.');
        redirect($redirectTo);
    }

    $target = max(1, (int)$habit['target']);

    // Increment the per-day repetition count, capped at the daily target.
    // First completion of the day inserts a row with count = 1; later ones increment.
    $stmt = $pdo->prepare("
        INSERT INTO habit_completions (habit_id, completion_date, count)
        VALUES (:habit_id, :completion_date, 1)
        ON DUPLICATE KEY UPDATE count = LEAST(:target, count + 1)
    ");

    $stmt->execute([
        'habit_id'        => $habitId,
        'completion_date' => $completionDate,
        'target'          => $target
    ]);

    // Read back the new count to report accurate progress
    $progressStmt = $pdo->prepare("SELECT count FROM habit_completions WHERE habit_id = :habit_id AND completion_date = :completion_date LIMIT 1");
    $progressStmt->execute([
        'habit_id'        => $habitId,
        'completion_date' => $completionDate
    ]);
    $currentCount = (int)$progressStmt->fetchColumn();

    if ($currentCount >= $target) {
        set_flash_message('success', $habit['name'] . ' fully completed today (' . $currentCount . '/' . $target . ')! Great job!');
    } else {
        set_flash_message('success', $habit['name'] . ' progress logged (' . $currentCount . '/' . $target . '). Keep going!');
    }
    redirect($redirectTo);

} catch (PDOException $e) {
    set_flash_message('error', 'Failed to mark habit as complete.');
    redirect($redirectTo);
}
