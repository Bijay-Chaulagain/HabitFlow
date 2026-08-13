<?php
/**
 * Admin System Statistics View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';

require_admin();

$pdo = getDBConnection();

// System Counts
$totalUsers       = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers      = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$inactiveUsers    = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();

$totalHabits      = (int)$pdo->query("SELECT COUNT(*) FROM habits")->fetchColumn();
$activeHabits     = (int)$pdo->query("SELECT COUNT(*) FROM habits WHERE status = 'active'")->fetchColumn();
$pausedHabits     = (int)$pdo->query("SELECT COUNT(*) FROM habits WHERE status = 'paused'")->fetchColumn();
$archivedHabits   = (int)$pdo->query("SELECT COUNT(*) FROM habits WHERE status = 'archived'")->fetchColumn();

$totalCompletions = (int)$pdo->query("SELECT COUNT(*) FROM habit_completions")->fetchColumn();

// Category Distribution
$categoryStats = $pdo->query("
    SELECT c.name, COUNT(h.id) AS habit_count
    FROM categories c
    LEFT JOIN habits h ON c.id = h.category_id
    GROUP BY c.id
    ORDER BY habit_count DESC
")->fetchAll();

$pageTitle = 'System Statistics';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">System Analytics & Platform Metrics</h1>
        <p class="page-subtitle">Comprehensive system-wide usage statistics and distribution metrics.</p>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- High-level Summary Metrics -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon primary"><?= icon('users', 20) ?></div>
        <div>
          <div class="stat-value"><?= $totalUsers ?></div>
          <div class="stat-label">Total Users (<?= $activeUsers ?> Active)</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon warning"><?= icon('target', 20) ?></div>
        <div>
          <div class="stat-value"><?= $totalHabits ?></div>
          <div class="stat-label">Total System Habits</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon success"><?= icon('check-circle', 20) ?></div>
        <div>
          <div class="stat-value"><?= $totalCompletions ?></div>
          <div class="stat-label">All-Time Completions</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon danger"><?= icon('user', 20) ?></div>
        <div>
          <div class="stat-value"><?= $inactiveUsers ?></div>
          <div class="stat-label">Inactive / Deactivated Users</div>
        </div>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
      
      <!-- Habit Status Breakdown -->
      <div class="card">
        <h2 class="card-title"><?= icon('chart', 16) ?> Habit Status Distribution</h2>
        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <span><?= icon('check-circle', 16) ?> Active Habits</span>
            <span class="badge badge-success"><?= $activeHabits ?></span>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <span><?= icon('clock', 16) ?> Paused Habits</span>
            <span class="badge badge-warning"><?= $pausedHabits ?></span>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <span><?= icon('archive', 16) ?> Archived Habits</span>
            <span class="badge badge-secondary"><?= $archivedHabits ?></span>
          </div>
        </div>
      </div>

      <!-- Category Popularity Distribution -->
      <div class="card">
        <h2 class="card-title"><?= icon('tags', 16) ?> Habits per Category</h2>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <?php foreach ($categoryStats as $cat): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 0.875rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
              <span style="font-weight: 600; font-size: 0.9375rem;"><?= e($cat['name']) ?></span>
              <span class="badge badge-primary"><?= (int)$cat['habit_count'] ?> habits</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>
</main>

<script src="/habit_tracker/assets/js/admin.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
