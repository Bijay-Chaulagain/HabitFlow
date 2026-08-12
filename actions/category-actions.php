<?php
/**
 * Admin Category CRUD Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_admin();

$action = clean_input($_REQUEST['action'] ?? '');

try {
    $pdo = getDBConnection();

    if ($action === 'add') {
        $name        = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');

        if (empty($name)) {
            set_flash_message('error', 'Category name is required.');
            redirect('/habit_tracker/admin/categories.php');
        }

        // Check for duplicate category name
        $dupStmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name LIMIT 1");
        $dupStmt->execute(['name' => $name]);
        if ($dupStmt->fetch()) {
            set_flash_message('error', 'Category with this name already exists.');
            redirect('/habit_tracker/admin/categories.php');
        }

        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
        $stmt->execute([
            'name'        => $name,
            'description' => !empty($description) ? $description : null
        ]);

        set_flash_message('success', 'Category "' . e($name) . '" created successfully.');
    } 
    else if ($action === 'update') {
        $id          = (int)($_POST['id'] ?? 0);
        $name        = clean_input($_POST['name'] ?? '');
        $description = clean_input($_POST['description'] ?? '');

        if ($id <= 0 || empty($name)) {
            set_flash_message('error', 'Invalid category data.');
            redirect('/habit_tracker/admin/categories.php');
        }

        // Check duplicate name on other categories
        $dupStmt = $pdo->prepare("SELECT id FROM categories WHERE name = :name AND id != :id LIMIT 1");
        $dupStmt->execute(['name' => $name, 'id' => $id]);
        if ($dupStmt->fetch()) {
            set_flash_message('error', 'Another category with this name already exists.');
            redirect('/habit_tracker/admin/categories.php');
        }

        $stmt = $pdo->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
        $stmt->execute([
            'name'        => $name,
            'description' => !empty($description) ? $description : null,
            'id'          => $id
        ]);

        set_flash_message('success', 'Category updated successfully.');
    } 
    else if ($action === 'delete') {
        $id = (int)($_REQUEST['id'] ?? 0);
        if ($id <= 0) {
            set_flash_message('error', 'Invalid category specified.');
            redirect('/habit_tracker/admin/categories.php');
        }

        // FOREIGN KEY DELETION PROTECTION CHECK: Check if any habits refer to this category
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM habits WHERE category_id = :id");
        $checkStmt->execute(['id' => $id]);
        $habitCount = (int)$checkStmt->fetchColumn();

        if ($habitCount > 0) {
            set_flash_message('error', 'Cannot delete category: ' . $habitCount . ' habit(s) are currently assigned to this category. Reassign or delete those habits first.');
            redirect('/habit_tracker/admin/categories.php');
        }

        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);

        set_flash_message('success', 'Category deleted successfully.');
    } 
    else {
        set_flash_message('error', 'Invalid category action.');
    }

    redirect('/habit_tracker/admin/categories.php');

} catch (PDOException $e) {
    set_flash_message('error', 'An error occurred while managing categories.');
    redirect('/habit_tracker/admin/categories.php');
}
