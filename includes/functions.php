<?php
/**
 * Helper Functions
 * Habit Tracker Web Application
 * 
 * Reusable utility functions for validation, formatting, streak calculations,
 * and flash messages.
 */

// Set application timezone explicitly
date_default_timezone_set('Asia/Kathmandu');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape output for HTML context (XSS Protection)
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Basic form string cleaning (trims leading/trailing whitespace).
 * Note: This is NOT for SQL injection or XSS security. 
 * SQL security is handled exclusively via PDO prepared statements.
 * HTML XSS escaping is handled exclusively via e().
 */
function clean_input(string $data): string {
    return trim($data);
}

/**
 * Redirect to a specific URL
 */
function redirect(string $url): void {
    header("Location: " . $url);
    exit();
}

/**
 * Set session flash message (success/error/warning/info)
 */
function set_flash_message(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Get and clear session flash message
 */
function get_flash_message(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML alert if present
 */
function display_flash_message(): void {
    $flash = get_flash_message();
    if ($flash) {
        $typeClass = $flash['type'] === 'error' ? 'alert-danger' : ($flash['type'] === 'success' ? 'alert-success' : 'alert-info');
        echo '<div class="alert ' . e($typeClass) . ' alert-dismissible" role="alert">';
        echo '<span>' . e($flash['message']) . '</span>';
        echo '<button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>';
        echo '</div>';
    }
}

/**
 * Validate email format
 */
function is_valid_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate username (alphanumeric, underscores, hyphens, 3-50 chars)
 */
function is_valid_username(string $username): bool {
    return preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $username) === 1;
}

/**
 * Validate password length
 */
function is_valid_password(string $password, int $minLength = 6): bool {
    return mb_strlen($password) >= $minLength;
}

/**
 * Get today's local date in YYYY-MM-DD format
 */
function get_today_date(): string {
    return date('Y-m-d');
}

/**
 * Format YYYY-MM-DD date to human readable format (e.g. Aug 11, 2026)
 */
function format_date(string $dateStr, string $format = 'M j, Y'): string {
    if (empty($dateStr)) return '';
    $timestamp = strtotime($dateStr);
    return $timestamp ? date($format, $timestamp) : $dateStr;
}

/**
 * Calculate Current Streak and Longest Streak from an array of completion dates (Y-m-d strings)
 * 
 * Algorithm:
 * 1. Filter and sort unique completion dates descending (newest first).
 * 2. Calculate current streak:
 *    - Check if completed today or yesterday. If neither, current streak = 0.
 *    - If completed today or yesterday, iterate backwards day-by-day counting consecutive days.
 * 3. Calculate longest streak:
 *    - Sort unique dates ascending (oldest first).
 *    - Iterate through sorted dates, checking if date[i] is consecutive to date[i-1].
 *    - Track maximum consecutive count reached.
 * 
 * @param array $completionDates Array of date strings ('YYYY-MM-DD')
 * @return array ['current_streak' => int, 'longest_streak' => int]
 */
function calculate_streaks(array $completionDates): array {
    if (empty($completionDates)) {
        return ['current_streak' => 0, 'longest_streak' => 0];
    }

    // Standardize, remove duplicates, sort descending for current streak
    $uniqueDates = array_values(array_unique($completionDates));
    rsort($uniqueDates); // Newest date first

    $today = new DateTime(date('Y-m-d'));
    $yesterday = (clone $today)->modify('-1 day');

    $currentStreak = 0;
    $longestStreak = 0;

    $mostRecentDate = new DateTime($uniqueDates[0]);
    $diffToday = (int)$today->diff($mostRecentDate)->format('%r%a');
    $diffYesterday = (int)$yesterday->diff($mostRecentDate)->format('%r%a');

    // Current streak is active if completed today (diff 0) or yesterday (diff 0 from yesterday)
    if ($diffToday === 0 || $diffYesterday === 0) {
        $checkDate = clone $mostRecentDate;
        $currentStreak = 1;

        for ($i = 1; $i < count($uniqueDates); $i++) {
            $prevExpectedDate = (clone $checkDate)->modify('-1 day');
            $actualDate = new DateTime($uniqueDates[$i]);

            if ($actualDate->format('Y-m-d') === $prevExpectedDate->format('Y-m-d')) {
                $currentStreak++;
                $checkDate = $actualDate;
            } else {
                break;
            }
        }
    }

    // Longest streak calculation (sort ascending)
    sort($uniqueDates); // Oldest date first
    $tempStreak = 1;
    $longestStreak = 1;

    for ($i = 1; $i < count($uniqueDates); $i++) {
        $prevDate = new DateTime($uniqueDates[$i - 1]);
        $currDate = new DateTime($uniqueDates[$i]);

        $diff = (int)$prevDate->diff($currDate)->format('%r%a');
        if ($diff === 1) {
            $tempStreak++;
        } else if ($diff > 1) {
            $tempStreak = 1;
        }

        if ($tempStreak > $longestStreak) {
            $longestStreak = $tempStreak;
        }
    }

    return [
        'current_streak' => $currentStreak,
        'longest_streak' => $longestStreak
    ];
}

/**
 * Check if a habit is marked complete on a specific date (Y-m-d)
 */
function is_habit_completed_on_date(PDO $pdo, int $habitId, string $dateStr): bool {
    $stmt = $pdo->prepare("SELECT id FROM habit_completions WHERE habit_id = :habit_id AND completion_date = :completion_date LIMIT 1");
    $stmt->execute([
        'habit_id'        => $habitId,
        'completion_date' => $dateStr
    ]);
    return $stmt->fetch() !== false;
}

/**
 * Get all completion dates for a habit as an array of YYYY-MM-DD strings
 */
function get_habit_completion_dates(PDO $pdo, int $habitId): array {
    $stmt = $pdo->prepare("SELECT completion_date FROM habit_completions WHERE habit_id = :habit_id ORDER BY completion_date DESC");
    $stmt->execute(['habit_id' => $habitId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

