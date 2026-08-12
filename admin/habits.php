<?php
/**
 * Admin System Habits Monitor View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = getDBConnection();

// Query all habits across all users with category & owner information
$stmt = $pdo->query("
    SELECT h.*, u.name AS user_name, u.email AS user_email, c.name AS category_name,
           COUNT(hc.id) AS completion_count
    FROM habits h
    JOIN users u ON h.user_id = u.id
    JOIN categories c ON h.category_id = c.id
    LEFT JOIN habit_completions hc ON h.id = hc.habit_id
    GROUP BY h.id
    ORDER BY h.created_at DESC
");
$allHabits = $stmt->fetchAll();

$pageTitle = 'System Habits Monitor';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">System Habits Monitor</h1>
        <p class="page-subtitle">Overview of all user habits registered across the system.</p>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="card">
      <?php if (empty($allHabits)): ?>
        <p style="color: var(--text-muted);">No habits recorded in the system yet.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Habit Name</th>
                <th>Owner</th>
                <th>Category</th>
                <th>Frequency</th>
                <th>Target</th>
                <th>Status</th>
                <th>Completions</th>
                <th>Start Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allHabits as $habit): ?>
                <tr>
                  <td><strong><?= e($habit['name']) ?></strong></td>
                  <td>
                    <?= e($habit['user_name']) ?>
                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= e($habit['user_email']) ?></div>
                  </td>
                  <td><span class="badge badge-primary"><?= e($habit['category_name']) ?></span></td>
                  <td><?= ucfirst(e($habit['frequency'])) ?></td>
                  <td><?= (int)$habit['target'] ?>x</td>
                  <td><span class="badge <?= $habit['status'] === 'active' ? 'badge-success' : ($habit['status'] === 'paused' ? 'badge-warning' : 'badge-secondary') ?>"><?= ucfirst(e($habit['status'])) ?></span></td>
                  <td><strong><?= (int)$habit['completion_count'] ?></strong></td>
                  <td><?= format_date($habit['start_date']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<script src="/habit_tracker/assets/js/admin.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
