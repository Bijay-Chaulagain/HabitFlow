<?php
/**
 * Edit Habit Page
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$habitId = (int)($_GET['id'] ?? 0);

if ($habitId <= 0) {
    set_flash_message('error', 'Invalid habit specified.');
    redirect('/habit_tracker/user/habits.php');
}

$pdo = getDBConnection();

// STRICT OWNERSHIP GUARD: Fetch habit ONLY if it belongs to current logged-in user
$stmt = $pdo->prepare("SELECT * FROM habits WHERE id = :id AND user_id = :user_id LIMIT 1");
$stmt->execute([
    'id'      => $habitId,
    'user_id' => $userId
]);
$habit = $stmt->fetch();

if (!$habit) {
    set_flash_message('error', 'Habit not found or access denied.');
    redirect('/habit_tracker/user/habits.php');
}

// Fetch categories for dropdown
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll();

$pageTitle = 'Edit Habit — ' . $habit['name'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Edit Habit</h1>
        <p class="page-subtitle">Update parameters for "<?= e($habit['name']) ?>"</p>
      </div>
      <div>
        <a href="/habit_tracker/user/habits.php" class="btn btn-outline">⬅️ Back to My Habits</a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="card" style="max-width: 680px;">
      <form id="editHabitForm" action="/habit_tracker/actions/update-habit.php" method="POST">
        <input type="hidden" name="habit_id" value="<?= (int)$habit['id'] ?>">

        <div class="form-group">
          <label for="name" class="form-label">Habit Name <span style="color: var(--danger);">*</span></label>
          <input type="text" id="name" name="name" class="form-control" value="<?= e($habit['name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="description" class="form-label">Description / Notes (Optional)</label>
          <textarea id="description" name="description" class="form-control" rows="3"><?= e($habit['description']) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="category_id" class="form-label">Category <span style="color: var(--danger);">*</span></label>
            <select id="category_id" name="category_id" class="form-control" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $habit['category_id'] ? 'selected' : '' ?>>
                  <?= e($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="frequency" class="form-label">Frequency <span style="color: var(--danger);">*</span></label>
            <select id="frequency" name="frequency" class="form-control" required>
              <option value="daily" <?= $habit['frequency'] === 'daily' ? 'selected' : '' ?>>Daily</option>
              <option value="weekly" <?= $habit['frequency'] === 'weekly' ? 'selected' : '' ?>>Weekly</option>
            </select>
          </div>

          <div class="form-group">
            <label for="status" class="form-label">Status <span style="color: var(--danger);">*</span></label>
            <select id="status" name="status" class="form-control" required>
              <option value="active" <?= $habit['status'] === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="paused" <?= $habit['status'] === 'paused' ? 'selected' : '' ?>>Paused</option>
              <option value="archived" <?= $habit['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="target" class="form-label">Target (x)</label>
            <input type="number" id="target" name="target" class="form-control" value="<?= (int)$habit['target'] ?>" min="1" max="100" required>
          </div>

          <div class="form-group">
            <label for="start_date" class="form-label">Start Date <span style="color: var(--danger);">*</span></label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= e($habit['start_date']) ?>" required>
          </div>

          <div class="form-group">
            <label for="reminder_time" class="form-label">Reminder Time</label>
            <input type="time" id="reminder_time" name="reminder_time" class="form-control" value="<?= e($habit['reminder_time']) ?>">
          </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
          <button type="submit" class="btn btn-primary">Update Habit</button>
          <a href="/habit_tracker/user/habits.php" class="btn btn-outline">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
