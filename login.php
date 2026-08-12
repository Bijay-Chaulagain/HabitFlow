<?php
/**
 * User Login Page
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/includes/auth.php';

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
  <meta name="description" content="Sign in to your Habit Tracker account to view your daily habits and progress.">
  <title>Sign In — Habit Tracker</title>
  <link rel="stylesheet" href="/habit_tracker/assets/css/style.css">
  <link rel="stylesheet" href="/habit_tracker/assets/css/auth.css">
</head>
<body>

  <div class="auth-wrapper">
    <div class="theme-toggle-auth">
      <button type="button" class="btn-icon js-theme-toggle" aria-label="Toggle dark mode">🌙</button>
    </div>

    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logo">
          ⚡ <span>Habit Tracker</span>
        </div>
        <p class="auth-subtitle">Sign in to continue to your dashboard</p>
      </div>

      <?php display_flash_message(); ?>

      <form id="loginForm" action="/habit_tracker/actions/login.php" method="POST" novalidate>
        <div class="form-group">
          <label for="username_email" class="form-label">Username or Email</label>
          <input type="text" id="username_email" name="username_email" class="form-control" placeholder="Enter username or email" required autofocus>
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Sign In</button>
      </form>

      <div class="auth-footer">
        Don't have an account? <a href="/habit_tracker/register.php">Create Account</a>
      </div>
    </div>
  </div>

  <script src="/habit_tracker/assets/js/theme.js"></script>
  <script src="/habit_tracker/assets/js/validation.js"></script>
</body>
</html>
