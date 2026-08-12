<?php
/**
 * Public Landing Page
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

if (is_logged_in()) {
    $currentUser = get_logged_in_user();
    if ($currentUser['role'] === 'admin') {
        redirect('/habit_tracker/admin/dashboard.php');
    } else {
        redirect('/habit_tracker/user/dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Habit Tracker — Build consistency, achieve your long-term goals, and track daily progress effortlessly.">
  <title>Habit Tracker — Build Better Daily Habits</title>
  <link rel="stylesheet" href="/habit_tracker/assets/css/style.css">
  <link rel="stylesheet" href="/habit_tracker/assets/css/auth.css">
</head>
<body>

  <div class="auth-wrapper">
    <div class="theme-toggle-auth">
      <button type="button" class="btn-icon js-theme-toggle" aria-label="Toggle dark mode"><?= icon('moon') ?></button>
    </div>

    <div class="auth-card auth-card-lg">
      <div class="auth-header">
        <div class="auth-logo">
          <?= icon('logo', 22) ?> <span>Habit Tracker</span>
        </div>
        <p class="auth-subtitle auth-subtitle-lg">
          Build consistency, track daily streaks, and achieve your goals with ease.
        </p>
      </div>

      <div class="auth-actions">
        <a href="/habit_tracker/register.php" class="btn btn-primary btn-full btn-lg">Get Started — Create Account</a>
        <a href="/habit_tracker/login.php" class="btn btn-outline btn-full btn-lg">Existing User? Sign In</a>
      </div>

      <div class="auth-footer auth-footer-spaced">
        Habit Tracker &copy; <?= date('Y') ?> — College Web Application Project
      </div>
    </div>
  </div>

  <script src="/habit_tracker/assets/js/theme.js"></script>
</body>
</html>