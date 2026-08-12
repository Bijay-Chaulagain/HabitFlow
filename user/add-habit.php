<?php
/**
 * Add Habit Page
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$pdo = getDBConnection();

// Fetch active categories for selection
$categoriesStmt = $pdo->query("SELECT id, name, description FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll();

$pageTitle = 'Add New Habit';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Add New Habit</h1>
        <p class="page-subtitle">Set up a new daily or weekly goal to build consistency.</p>
      </div>
      <div>
        <a href="/habit_tracker/user/habits.php" class="btn btn-outline">⬅️ Back to My Habits</a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="card" style="max-width: 680px;">
      <form id="addHabitForm" action="/habit_tracker/actions/add-habit.php" method="POST">
        
        <div class="form-group">
          <label for="name" class="form-label">Habit Name <span style="color: var(--danger);">*</span></label>
          <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Read 20 Pages, Morning Workout, Drink Water" required autofocus>
        </div>

        <div class="form-group">
          <label for="description" class="form-label">Description / Notes (Optional)</label>
          <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief details or motivation for this habit..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="category_id" class="form-label">Category <span style="color: var(--danger);">*</span></label>
            <select id="category_id" name="category_id" class="form-control" required>
              <option value="">-- Select Category --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="frequency" class="form-label">Frequency <span style="color: var(--danger);">*</span></label>
            <select id="frequency" name="frequency" class="form-control" required>
              <option value="daily" selected>Daily</option>
              <option value="weekly">Weekly</option>
            </select>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
          <div class="form-group">
            <label for="target" class="form-label">Daily Target (x)</label>
            <input type="number" id="target" name="target" class="form-control" value="1" min="1" max="100" required>
          </div>

          <div class="form-group">
            <label for="start_date" class="form-label">Start Date <span style="color: var(--danger);">*</span></label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= get_today_date() ?>" required>
          </div>

          <div class="form-group">
            <label for="reminder_time" class="form-label">Reminder Time</label>
            <input type="time" id="reminder_time" name="reminder_time" class="form-control">
          </div>
        </div>

        <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
          <button type="submit" class="btn btn-primary">Save Habit</button>
          <a href="/habit_tracker/user/habits.php" class="btn btn-outline">Cancel</a>
        </div>

      </form>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
