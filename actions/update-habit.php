<?php
/**
 * Update Habit Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/habit_tracker/user/habits.php');
}

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$habitId      = (int)($_POST['habit_id'] ?? 0);
$name         = clean_input($_POST['name'] ?? '');
$description  = clean_input($_POST['description'] ?? '');
$categoryId   = (int)($_POST['category_id'] ?? 0);
$frequency    = clean_input($_POST['frequency'] ?? 'daily');
$target       = max(1, (int)($_POST['target'] ?? 1));
$startDate    = clean_input($_POST['start_date'] ?? get_today_date());
$reminderTime = !empty($_POST['reminder_time']) ? $_POST['reminder_time'] : null;
$status       = clean_input($_POST['status'] ?? 'active');

if ($habitId <= 0 || empty($name) || $categoryId <= 0) {
    set_flash_message('error', 'Please fill in all required fields.');
    redirect('/habit_tracker/user/edit-habit.php?id=' . $habitId);
}

if (!in_array($frequency, ['daily', 'weekly'])) {
    $frequency = 'daily';
}

if (!in_array($status, ['active', 'paused', 'archived'])) {
    $status = 'active';
}

try {
    $pdo = getDBConnection();

    // STRICT OWNERSHIP ENFORCEMENT: Verify habit exists and belongs to current logged-in user
    if (!verify_habit_ownership($pdo, $habitId, $userId)) {
        set_flash_message('error', 'You are not authorized to modify this habit.');
        redirect('/habit_tracker/user/habits.php');
    }

    // Verify category exists
    $catStmt = $pdo->prepare("SELECT id FROM categories WHERE id = :id");
    $catStmt->execute(['id' => $categoryId]);
    if (!$catStmt->fetch()) {
        set_flash_message('error', 'Selected category does not exist.');
        redirect('/habit_tracker/user/edit-habit.php?id=' . $habitId);
    }

    // Update habit
    $stmt = $pdo->prepare("
        UPDATE habits 
        SET category_id = :category_id,
            name = :name,
            description = :description,
            frequency = :frequency,
            target = :target,
            start_date = :start_date,
            reminder_time = :reminder_time,
            status = :status
        WHERE id = :habit_id AND user_id = :user_id
    ");

    $stmt->execute([
        'category_id'   => $categoryId,
        'name'          => $name,
        'description'   => !empty($description) ? $description : null,
        'frequency'     => $frequency,
        'target'        => $target,
        'start_date'    => $startDate,
        'reminder_time' => $reminderTime,
        'status'        => $status,
        'habit_id'      => $habitId,
        'user_id'       => $userId
    ]);

    set_flash_message('success', 'Habit updated successfully!');
    redirect('/habit_tracker/user/habits.php');

} catch (PDOException $e) {
    set_flash_message('error', 'Failed to update habit. Please try again.');
    redirect('/habit_tracker/user/edit-habit.php?id=' . $habitId);
}
