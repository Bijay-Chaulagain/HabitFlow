<?php
/**
 * Admin Category Management View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = getDBConnection();

// Fetch categories with associated habit counts
$stmt = $pdo->query("
    SELECT c.*, COUNT(h.id) AS habit_count
    FROM categories c
    LEFT JOIN habits h ON c.id = h.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();

// Edit category request check
$editCategory = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $editStmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
    $editStmt->execute(['id' => $editId]);
    $editCategory = $editStmt->fetch();
}

$pageTitle = 'Category Management';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Category Management</h1>
        <p class="page-subtitle">Add, update, or remove habit categories for the system.</p>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
      
      <!-- Category Add / Edit Form -->
      <div class="card" style="height: fit-content;">
        <h2 class="card-title"><?= $editCategory ? '✏️ Edit Category' : '➕ Create New Category' ?></h2>

        <form action="/habit_tracker/actions/category-actions.php" method="POST">
          <input type="hidden" name="action" value="<?= $editCategory ? 'update' : 'add' ?>">
          <?php if ($editCategory): ?>
            <input type="hidden" name="id" value="<?= (int)$editCategory['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label for="name" class="form-label">Category Name <span style="color: var(--danger);">*</span></label>
            <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Wellness, Productivity" value="<?= e($editCategory['name'] ?? '') ?>" required>
          </div>

          <div class="form-group">
            <label for="description" class="form-label">Description (Optional)</label>
            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Brief description of habits in this category..."><?= e($editCategory['description'] ?? '') ?></textarea>
          </div>

          <div style="display: flex; gap: 0.75rem;">
            <button type="submit" class="btn btn-primary btn-full">
              <?= $editCategory ? 'Update Category' : 'Save Category' ?>
            </button>
            <?php if ($editCategory): ?>
              <a href="/habit_tracker/admin/categories.php" class="btn btn-outline">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- Categories List Cards Grid -->
      <div class="card">
        <h2 class="card-title">Existing Categories (<?= count($categories) ?>)</h2>

        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
          <?php foreach ($categories as $cat): ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background-color: var(--bg-surface);">
              <div>
                <div style="font-size: 1.05rem; font-weight: 700; color: var(--text-main);">
                  <?= e($cat['name']) ?>
                  <span class="badge badge-primary" style="margin-left: 0.5rem; font-size: 0.75rem;">
                    <?= (int)$cat['habit_count'] ?> habits
                  </span>
                </div>
                <?php if (!empty($cat['description'])): ?>
                  <div style="font-size: 0.84375rem; color: var(--text-muted); margin-top: 0.25rem;">
                    <?= e($cat['description']) ?>
                  </div>
                <?php endif; ?>
              </div>

              <div style="display: flex; gap: 0.5rem;">
                <a href="/habit_tracker/admin/categories.php?edit_id=<?= $cat['id'] ?>" class="btn btn-outline btn-sm">
                  ✏️ Edit
                </a>
                
                <a href="/habit_tracker/actions/category-actions.php?action=delete&id=<?= $cat['id'] ?>" class="btn btn-danger btn-sm js-confirm-category-delete">
                  🗑️
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>
</main>

<script src="/habit_tracker/assets/js/admin.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
