<?php
/**
 * Shared Sidebar Navigation Template
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/auth.php';

$user = get_logged_in_user();
$currentRole = $user['role'] ?? 'user';
$currentScript = basename($_SERVER['SCRIPT_NAME']);
?>
<aside class="app-sidebar">
  <div class="sidebar-brand">
    ⚡ <span>Habit Tracker</span>
  </div>

  <ul class="sidebar-menu">
    <?php if ($currentRole === 'admin'): ?>
      <!-- Admin Navigation Links -->
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/dashboard.php" class="sidebar-link <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
          📊 Dashboard
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/users.php" class="sidebar-link <?= $currentScript === 'users.php' ? 'active' : '' ?>">
          👥 Users Management
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/habits.php" class="sidebar-link <?= $currentScript === 'habits.php' ? 'active' : '' ?>">
          📝 System Habits
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/categories.php" class="sidebar-link <?= $currentScript === 'categories.php' ? 'active' : '' ?>">
          🏷️ Categories
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/admin/statistics.php" class="sidebar-link <?= $currentScript === 'statistics.php' ? 'active' : '' ?>">
          📈 System Statistics
        </a>
      </li>
    <?php else: ?>
      <!-- Normal User Navigation Links -->
      <li class="sidebar-item">
        <a href="/habit_tracker/user/dashboard.php" class="sidebar-link <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
          🏠 Dashboard
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/habits.php" class="sidebar-link <?= $currentScript === 'habits.php' ? 'active' : '' ?>">
          🎯 My Habits
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/add-habit.php" class="sidebar-link <?= $currentScript === 'add-habit.php' ? 'active' : '' ?>">
          ➕ Add Habit
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/calendar.php" class="sidebar-link <?= $currentScript === 'calendar.php' ? 'active' : '' ?>">
          📅 Calendar
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/statistics.php" class="sidebar-link <?= $currentScript === 'statistics.php' ? 'active' : '' ?>">
          📈 Statistics
        </a>
      </li>
      <li class="sidebar-item">
        <a href="/habit_tracker/user/profile.php" class="sidebar-link <?= $currentScript === 'profile.php' ? 'active' : '' ?>">
          👤 Profile Settings
        </a>
      </li>
    <?php endif; ?>
    
    <li class="sidebar-item" style="margin-top: 1.5rem;">
      <a href="/habit_tracker/logout.php" class="sidebar-link" style="color: #ef4444;">
        🚪 Logout
      </a>
    </li>
  </ul>

  <div class="sidebar-footer">
    Habit Tracker v1.0 &bull; XAMPP Native
  </div>
</aside>
