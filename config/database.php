<?php
/**
 * Database Connection Configuration
 * Habit Tracker Web Application
 * 
 * Establishes a secure PDO connection to MySQL database.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'habit_tracker');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error internally in production, display user-friendly message
            die("Database Connection Error: Unable to connect to the database. Please ensure XAMPP MySQL is running.");
        }
    }

    return $pdo;
}

// Global PDO instance for quick access
$pdo = getDBConnection();
