<?php
/**
 * Add Habit Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/habit_tracker/user/habits.php');
}

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$name         = clean_input($_POST['name'] ?? '');
$description  = clean_input($_POST['description'] ?? '');
$categoryId   = (int)($_POST['category_id'] ?? 0);
$frequency    = clean_input($_POST['frequency'] ?? 'daily');
$target       = max(1, (int)($_POST['target'] ?? 1));
$startDate    = clean_input($_POST['start_date'] ?? get_today_date());
$reminderTime = !empty($_POST['reminder_time']) ? $_POST['reminder_time'] : null;

// Validation
if (empty($name)) {
    set_flash_message('error', 'Habit name is required.');
    redirect('/habit_tracker/user/add-habit.php');
}

if ($categoryId <= 0) {
    set_flash_message('error', 'Please select a valid habit category.');
    redirect('/habit_tracker/user/add-habit.php');
}

if (!in_array($frequency, ['daily', 'weekly'])) {
    $frequency = 'daily';
}

try {
    $pdo = getDBConnection();

    // Verify category exists
    $catStmt = $pdo->prepare("SELECT id FROM categories WHERE id = :id");
    $catStmt->execute(['id' => $categoryId]);
    if (!$catStmt->fetch()) {
        set_flash_message('error', 'Selected category does not exist.');
        redirect('/habit_tracker/user/add-habit.php');
    }

    // Insert habit
    $stmt = $pdo->prepare("
        INSERT INTO habits (user_id, category_id, name, description, frequency, target, start_date, reminder_time, status)
        VALUES (:user_id, :category_id, :name, :description, :frequency, :target, :start_date, :reminder_time, 'active')
    ");

    $stmt->execute([
        'user_id'       => $userId,
        'category_id'   => $categoryId,
        'name'          => $name,
        'description'   => !empty($description) ? $description : null,
        'frequency'     => $frequency,
        'target'        => $target,
        'start_date'    => $startDate,
        'reminder_time' => $reminderTime
    ]);

    set_flash_message('success', 'Habit created successfully!');
    redirect('/habit_tracker/user/habits.php');

} catch (PDOException $e) {
    set_flash_message('error', 'Failed to create habit. Please try again.');
    redirect('/habit_tracker/user/add-habit.php');
}
