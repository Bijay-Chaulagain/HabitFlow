<?php
/**
 * Admin User Management View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$currentAdmin = get_logged_in_user();
$currentAdminId = $currentAdmin['id'];

$pdo = getDBConnection();

// Fetch all users with habit and completion stats
$stmt = $pdo->query("
    SELECT u.id, u.name, u.username, u.email, u.role, u.status, u.created_at,
           COUNT(DISTINCT h.id) AS habit_count
    FROM users u
    LEFT JOIN habits h ON u.id = h.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">View system users, activate/deactivate accounts, and inspect habit activity.</p>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="card">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Habits</th>
              <th>Status</th>
              <th>Registered</th>
              <th style="text-align: right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td>#<?= (int)$user['id'] ?></td>
                <td><strong><?= e($user['name']) ?></strong></td>
                <td><?= e($user['username']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td><span class="badge <?= $user['role'] === 'admin' ? 'badge-danger' : 'badge-primary' ?>"><?= ucfirst(e($user['role'])) ?></span></td>
                <td><?= (int)$user['habit_count'] ?> habits</td>
                <td><span class="badge <?= $user['status'] === 'active' ? 'badge-success' : 'badge-secondary' ?>"><?= ucfirst(e($user['status'])) ?></span></td>
                <td><?= format_date($user['created_at']) ?></td>
                <td style="text-align: right;">
                  <?php if ($user['id'] === $currentAdminId): ?>
                    <span style="font-size: 0.8125rem; color: var(--text-muted); font-style: italic;">(You)</span>
                  <?php else: ?>
                    <?php if ($user['status'] === 'active'): ?>
                      <a href="/habit_tracker/actions/user-actions.php?id=<?= $user['id'] ?>&action=deactivate" class="btn btn-outline btn-sm js-confirm-user-status" data-action="deactivate" style="color: var(--warning);" title="Deactivate Account">
                        Deactivate
                      </a>
                    <?php else: ?>
                      <a href="/habit_tracker/actions/user-actions.php?id=<?= $user['id'] ?>&action=activate" class="btn btn-success btn-sm js-confirm-user-status" data-action="activate" title="Activate Account">
                        Activate
                      </a>
                    <?php endif; ?>

                    <a href="/habit_tracker/actions/user-actions.php?id=<?= $user['id'] ?>&action=delete" class="btn btn-danger btn-sm js-confirm-user-status" data-action="permanently delete" title="Delete User">
                      🗑️
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<script src="/habit_tracker/assets/js/admin.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
