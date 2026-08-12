<?php
/**
 * User Profile & Settings View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$pdo = getDBConnection();

// Fetch full user profile from database
$stmt = $pdo->prepare("SELECT id, name, username, email, role, status, created_at FROM users WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $userId]);
$profile = $stmt->fetch();

if (!$profile) {
    set_flash_message('error', 'Profile not found.');
    redirect('/habit_tracker/user/dashboard.php');
}

// Fetch user habit & completion counts
$habitCountStmt = $pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = :user_id");
$habitCountStmt->execute(['user_id' => $userId]);
$totalHabits = (int)$habitCountStmt->fetchColumn();

$completionCountStmt = $pdo->prepare("
    SELECT COUNT(hc.id) FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id
");
$completionCountStmt->execute(['user_id' => $userId]);
$totalCompletions = (int)$completionCountStmt->fetchColumn();

$pageTitle = 'Profile Settings';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Profile & Account Settings</h1>
        <p class="page-subtitle">View your account information, update your name, or change your password.</p>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

      <!-- Profile Overview Card -->
      <div class="card">
        <h2 class="card-title">👤 Account Overview</h2>

        <div style="display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.5rem; padding: 1.25rem; background-color: var(--bg-main); border-radius: var(--border-radius);">
          <div class="user-avatar" style="width: 64px; height: 64px; font-size: 1.5rem;">
            <?= strtoupper(substr(e($profile['name']), 0, 1)) ?>
          </div>
          <div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-main);"><?= e($profile['name']) ?></div>
            <div style="font-size: 0.875rem; color: var(--text-muted);">@<?= e($profile['username']) ?> &bull; <?= e($profile['email']) ?></div>
            <div style="margin-top: 0.375rem;">
              <span class="badge badge-primary"><?= ucfirst(e($profile['role'])) ?></span>
              <span class="badge badge-success" style="margin-left: 0.25rem;"><?= ucfirst(e($profile['status'])) ?></span>
            </div>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
          <div style="text-align: center; padding: 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?= $totalHabits ?></div>
            <div style="font-size: 0.8125rem; color: var(--text-muted);">Total Habits</div>
          </div>
          <div style="text-align: center; padding: 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--success);"><?= $totalCompletions ?></div>
            <div style="font-size: 0.8125rem; color: var(--text-muted);">Completions</div>
          </div>
          <div style="text-align: center; padding: 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);"><?= format_date($profile['created_at'], 'M Y') ?></div>
            <div style="font-size: 0.8125rem; color: var(--text-muted);">Member Since</div>
          </div>
        </div>
      </div>

      <!-- Edit Profile & Change Password Forms Column -->
      <div>

        <!-- Edit Name Form -->
        <div class="card">
          <h2 class="card-title">✏️ Edit Profile</h2>
          <form action="/habit_tracker/actions/profile.php" method="POST">
            <div class="form-group">
              <label for="name" class="form-label">Full Name</label>
              <input type="text" id="name" name="name" class="form-control" value="<?= e($profile['name']) ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label">Username</label>
              <input type="text" class="form-control" value="<?= e($profile['username']) ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Username cannot be changed.</div>
            </div>

            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="text" class="form-control" value="<?= e($profile['email']) ?>" disabled style="opacity: 0.6; cursor: not-allowed;">
              <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Email cannot be changed.</div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Update Profile</button>
          </form>
        </div>

        <!-- Change Password Form -->
        <div class="card">
          <h2 class="card-title">🔒 Change Password</h2>
          <form id="changePasswordForm" action="/habit_tracker/actions/password.php" method="POST">
            <div class="form-group">
              <label for="current_password" class="form-label">Current Password <span style="color: var(--danger);">*</span></label>
              <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" required>
            </div>

            <div class="form-group">
              <label for="new_password" class="form-label">New Password <span style="color: var(--danger);">*</span></label>
              <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
            </div>

            <div class="form-group">
              <label for="confirm_password" class="form-label">Confirm New Password <span style="color: var(--danger);">*</span></label>
              <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password" required>
            </div>

            <button type="submit" class="btn btn-danger btn-full">Change Password</button>
          </form>
        </div>

      </div>

    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
