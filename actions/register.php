<?php
/**
 * Registration Action Processing
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/habit_tracker/register.php');
}

$name            = clean_input($_POST['name'] ?? '');
$username        = clean_input($_POST['username'] ?? '');
$email           = clean_input($_POST['email'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate required fields
if (empty($name) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
    set_flash_message('error', 'All fields are required.');
    redirect('/habit_tracker/register.php');
}

// Validate email format
if (!is_valid_email($email)) {
    set_flash_message('error', 'Please enter a valid email address.');
    redirect('/habit_tracker/register.php');
}

// Validate username format
if (!is_valid_username($username)) {
    set_flash_message('error', 'Username must be 3-50 characters containing only letters, numbers, hyphens, or underscores.');
    redirect('/habit_tracker/register.php');
}

// Validate password minimum length
if (!is_valid_password($password, 6)) {
    set_flash_message('error', 'Password must be at least 6 characters long.');
    redirect('/habit_tracker/register.php');
}

// Validate password confirmation match
if ($password !== $confirmPassword) {
    set_flash_message('error', 'Password and Confirm Password do not match.');
    redirect('/habit_tracker/register.php');
}

try {
    $pdo = getDBConnection();

    // Check for existing username or email using PDO prepared statements
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1");
    $stmt->execute([
        'username' => $username,
        'email'    => $email
    ]);

    $existingUser = $stmt->fetch();
    if ($existingUser) {
        if ($existingUser['username'] === $username) {
            set_flash_message('error', 'Username is already taken.');
        } else {
            set_flash_message('error', 'Email is already registered.');
        }
        redirect('/habit_tracker/register.php');
    }

    // Hash password securely using BCRYPT
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert user into database
    $insertStmt = $pdo->prepare("
        INSERT INTO users (name, username, email, password, role, status)
        VALUES (:name, :username, :email, :password, 'user', 'active')
    ");

    $insertStmt->execute([
        'name'     => $name,
        'username' => $username,
        'email'    => $email,
        'password' => $hashedPassword
    ]);

    set_flash_message('success', 'Registration successful! You can now log in.');
    redirect('/habit_tracker/login.php');

} catch (PDOException $e) {
    set_flash_message('error', 'An error occurred during registration. Please try again.');
    redirect('/habit_tracker/register.php');
}
