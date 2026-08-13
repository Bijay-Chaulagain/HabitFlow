<?php
/**
 * Admin Overview Dashboard
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';

require_admin();

$pdo = getDBConnection();

// Summary Metrics
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$totalHabits = (int)$pdo->query("SELECT COUNT(*) FROM habits")->fetchColumn();
$totalCompletions = (int)$pdo->query("SELECT COUNT(*) FROM habit_completions")->fetchColumn();

// Popular Categories
$popularCategories = $pdo->query("
    SELECT c.name, COUNT(h.id) AS habit_count
    FROM categories c
    LEFT JOIN habits h ON c.id = h.category_id
    GROUP BY c.id
    ORDER BY habit_count DESC
    LIMIT 5
")->fetchAll();

// Recent Users
$recentUsers = $pdo->query("
    SELECT id, name, username, email, role, status, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Administrator Dashboard</h1>
        <p class="page-subtitle">System metrics, user management, and overall platform activity.</p>
      </div>
      <div>
        <a href="/habit_tracker/admin/categories.php" class="btn btn-primary"><?= icon('tags') ?> Manage Categories</a>
        <a href="/habit_tracker/admin/users.php" class="btn btn-outline"><?= icon('users') ?> Manage Users</a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- System Metrics Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon primary"><?= icon('users', 20) ?></div>
        <div>
          <div class="stat-value"><?= $totalUsers ?></div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon success"><?= icon('user', 20) ?></div>
        <div>
          <div class="stat-value"><?= $activeUsers ?></div>
          <div class="stat-label">Active Users</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon warning"><?= icon('target', 20) ?></div>
        <div>
          <div class="stat-value"><?= $totalHabits ?></div>
          <div class="stat-label">Total Habits</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon success"><?= icon('check-circle', 20) ?></div>
        <div>
          <div class="stat-value"><?= $totalCompletions ?></div>
          <div class="stat-label">Total Completions</div>
        </div>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
      
      <!-- Recent Users Card -->
      <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h2 class="card-title" style="margin-bottom: 0;">Recent User Registrations</h2>
          <a href="/habit_tracker/admin/users.php" style="font-size: 0.875rem; font-weight: 600;">View All Users &rarr;</a>
        </div>

        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentUsers as $user): ?>
                <tr>
                  <td><strong><?= e($user['name']) ?></strong></td>
                  <td><?= e($user['username']) ?></td>
                  <td><span class="badge <?= $user['role'] === 'admin' ? 'badge-danger' : 'badge-primary' ?>"><?= ucfirst(e($user['role'])) ?></span></td>
                  <td><span class="badge <?= $user['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>"><?= ucfirst(e($user['status'])) ?></span></td>
                  <td><?= format_date($user['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Popular Categories Card -->
      <div class="card">
        <h2 class="card-title"><?= icon('tags', 16) ?> Popular Categories</h2>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
          <?php foreach ($popularCategories as $cat): ?>
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
