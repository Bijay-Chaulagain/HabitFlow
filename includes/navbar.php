<?php
/**
 * Shared Top Navigation Bar Template
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/icons.php';

$user = get_logged_in_user();
$userName = $user['name'] ?? 'User';
$userRole = $user['role'] ?? 'user';
$userInitial = strtoupper(substr($userName, 0, 1));
?>
<header class="app-header">
  <div class="header-date">
    <?= icon('calendar', 16) ?>
    <span><?= date('l, M j, Y') ?></span>
  </div>

  <div class="header-user">
    <button type="button" class="btn-icon js-theme-toggle" aria-label="Toggle dark mode"><?= icon('moon', 18) ?></button>
    
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <div class="user-avatar"><?= e($userInitial) ?></div>
      <div class="user-info">
        <div class="user-name"><?= e($userName) ?></div>
        <div class="user-role"><?= e($userRole) ?></div>
      </div>
    </div>
  </div>
</header>