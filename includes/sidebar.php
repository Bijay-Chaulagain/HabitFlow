<?php
/**
 * Shared Sidebar Navigation Template
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/icons.php';

$user = get_logged_in_user();
$currentRole = $user['role'] ?? 'user';
$currentScript = basename($_SERVER['SCRIPT_NAME']);
?>
<aside class="app-sidebar">
  <div class="sidebar-brand">
    <?= icon('logo', 20) ?> <span>Habit Tracker</span>
  </div>

  <ul class="sidebar-menu">
    <?php if ($currentRole === 'admin'): ?>
      <!-- Admin Navigation Links -->
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/dashboard.php" class="sidebar-link <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
          <?= icon('home') ?> Dashboard
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/users.php" class="sidebar-link <?= $currentScript === 'users.php' ? 'active' : '' ?>">
          <?= icon('users') ?> Users Management
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/habits.php" class="sidebar-link <?= $currentScript === 'habits.php' ? 'active' : '' ?>">
          <?= icon('file') ?> System Habits
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/categories.php" class="sidebar-link <?= $currentScript === 'categories.php' ? 'active' : '' ?>">
          <?= icon('tags') ?> Categories
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/statistics.php" class="sidebar-link <?= $currentScript === 'statistics.php' ? 'active' : '' ?>">
          <?= icon('chart') ?> System Statistics
        </a>
      </li>
    <?php else: ?>
      <!-- Normal User Navigation Links -->
      <li class="sidebar-item">
        <a href="/habit_tracker/user/dashboard.php" class="sidebar-link <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
          <?= icon('home') ?> Dashboard
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/habits.php" class="sidebar-link <?= $currentScript === 'habits.php' ? 'active' : '' ?>">
          <?= icon('target') ?> My Habits
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/add-habit.php" class="sidebar-link <?= $currentScript === 'add-habit.php' ? 'active' : '' ?>">
          <?= icon('plus') ?> Add Habit
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/calendar.php" class="sidebar-link <?= $currentScript === 'calendar.php' ? 'active' : '' ?>">
          <?= icon('calendar') ?> Calendar
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/statistics.php" class="sidebar-link <?= $currentScript === 'statistics.php' ? 'active' : '' ?>">
          <?= icon('chart') ?> Statistics
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/profile.php" class="sidebar-link <?= $currentScript === 'profile.php' ? 'active' : '' ?>">
          <?= icon('user') ?> Profile Settings
        </a>
      </li>
    <?php endif; ?>
    
    <li class="sidebar-item" style="margin-top: 1.5rem;">
      <a href="/habit_tracker/logout.php" class="sidebar-link sidebar-link-logout">
        <?= icon('logout') ?> Logout
      </a>
    </li>
  </ul>

  <div class="sidebar-footer">
    Habit Tracker v1.0 &bull; XAMPP Native
  </div>
</aside>