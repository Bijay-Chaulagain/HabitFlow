<?php
/**
 * User Registration Page
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

// Redirect logged-in users to their respective dashboard
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
  <meta name="description" content="Register for Habit Tracker to build healthy habits, track streaks, and achieve your goals.">
  <title>Create Account — Habit Tracker</title>
  <link rel="stylesheet" href="/habit_tracker/assets/css/style.css">
  <link rel="stylesheet" href="/habit_tracker/assets/css/auth.css">
</head>
<body>

  <div class="auth-wrapper">
    <div class="theme-toggle-auth">
      <button type="button" class="btn-icon js-theme-toggle" aria-label="Toggle dark mode"><?= icon('moon') ?></button>
    </div>

    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logo">
          <?= icon('logo', 22) ?> <span>Habit Tracker</span>
        </div>
        <p class="auth-subtitle">Create an account to start tracking your habits</p>
      </div>

      <?php display_flash_message(); ?>

      <form id="registerForm" action="/habit_tracker/actions/register.php" method="POST" novalidate>
        <div class="form-group">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" required>
        </div>

        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="johndoe" required>
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com" required>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
        </div>

        <div class="form-group">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Create Account</button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="/habit_tracker/login.php">Sign In</a>
      </div>
    </div>
  </div>

  <script src="/habit_tracker/assets/js/theme.js"></script>
  <script src="/habit_tracker/assets/js/validation.js"></script>
</body>
</html>