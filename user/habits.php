<?php
/**
 * User Habits Management List View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$statusFilter = clean_input($_GET['status'] ?? 'active');
$allowedFilters = ['active', 'paused', 'archived', 'all'];
if (!in_array($statusFilter, $allowedFilters)) {
    $statusFilter = 'active';
}

$pdo = getDBConnection();

// Fetch categories for filter reference
$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll();

// Build habits query for current logged-in user
$query = "
    SELECT h.*, c.name AS category_name 
    FROM habits h
    JOIN categories c ON h.category_id = c.id
    WHERE h.user_id = :user_id
";

$params = ['user_id' => $userId];

if ($statusFilter !== 'all') {
    $query .= " AND h.status = :status";
    $params['status'] = $statusFilter;
}

$query .= " ORDER BY h.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$habits = $stmt->fetchAll();

$pageTitle = 'My Habits';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">My Habits</h1>
        <p class="page-subtitle">Manage, edit, and track all your personal habits.</p>
      </div>
      <div>
        <a href="/habit_tracker/user/add-habit.php" class="btn btn-primary">➕ Create New Habit</a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- Status Filter Tabs -->
    <div class="card" style="padding: 0.75rem 1.25rem; margin-bottom: 1.5rem;">
      <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="/habit_tracker/user/habits.php?status=active" class="btn btn-sm <?= $statusFilter === 'active' ? 'btn-primary' : 'btn-outline' ?>">
          Active
        </a>
        <a href="/habit_tracker/user/habits.php?status=paused" class="btn btn-sm <?= $statusFilter === 'paused' ? 'btn-primary' : 'btn-outline' ?>">
          Paused
        </a>
        <a href="/habit_tracker/user/habits.php?status=archived" class="btn btn-sm <?= $statusFilter === 'archived' ? 'btn-primary' : 'btn-outline' ?>">
          Archived
        </a>
        <a href="/habit_tracker/user/habits.php?status=all" class="btn btn-sm <?= $statusFilter === 'all' ? 'btn-primary' : 'btn-outline' ?>">
          All Habits
        </a>
      </div>
    </div>

    <?php if (empty($habits)): ?>
      <div class="empty-state">
        <div class="empty-state-icon">🎯</div>
        <h3 class="empty-state-title">No habits found</h3>
        <p class="empty-state-desc">
          <?= $statusFilter === 'archived' ? 'You don\'t have any archived habits.' : 'You haven\'t created any habits yet. Start tracking your consistency today!' ?>
        </p>
        <a href="/habit_tracker/user/add-habit.php" class="btn btn-primary">Create Your First Habit</a>
      </div>
    <?php else: ?>
      <div class="habit-grid">
        <?php foreach ($habits as $habit): ?>
          <div class="habit-card">
            <div>
              <div class="habit-header">
                <h3 class="habit-name"><?= e($habit['name']) ?></h3>
                <span class="badge badge-primary"><?= e($habit['category_name']) ?></span>
              </div>

              <?php if (!empty($habit['description'])): ?>
                <p class="habit-desc"><?= e($habit['description']) ?></p>
              <?php endif; ?>

              <div class="habit-meta">
                <div class="habit-meta-item">
                  🔁 <strong><?= ucfirst(e($habit['frequency'])) ?></strong> (<?= (int)$habit['target'] ?>x)
                </div>
                <div class="habit-meta-item">
                  📅 Started <?= format_date($habit['start_date']) ?>
                </div>
                <?php if (!empty($habit['reminder_time'])): ?>
                  <div class="habit-meta-item">
                    ⏰ <?= date('g:i A', strtotime($habit['reminder_time'])) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <div class="habit-actions">
              <span class="badge <?= $habit['status'] === 'active' ? 'badge-success' : ($habit['status'] === 'paused' ? 'badge-warning' : 'badge-secondary') ?>" style="margin-right: auto;">
                <?= ucfirst(e($habit['status'])) ?>
              </span>

              <a href="/habit_tracker/user/edit-habit.php?id=<?= $habit['id'] ?>" class="btn btn-outline btn-sm" title="Edit Habit">
                ✏️ Edit
              </a>

              <?php if ($habit['status'] !== 'archived'): ?>
                <a href="/habit_tracker/actions/delete-habit.php?id=<?= $habit['id'] ?>&action=archive" class="btn btn-outline btn-sm js-confirm-archive" title="Archive Habit">
                  📦 Archive
                </a>
              <?php endif; ?>

              <a href="/habit_tracker/actions/delete-habit.php?id=<?= $habit['id'] ?>&action=delete" class="btn btn-danger btn-sm js-confirm-delete" title="Delete Permanently">
                🗑️
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
