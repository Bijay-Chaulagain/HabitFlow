<?php
/**
 * Shared Header Template
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/auth.php';

$currentUser = get_logged_in_user();
$pageTitle = $pageTitle ?? 'Habit Tracker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Habit Tracker — Build consistency and track your progress daily.">
  <title><?= e($pageTitle) ?> — Habit Tracker</title>
  <link rel="stylesheet" href="/habit_tracker/assets/css/style.css">
  <link rel="stylesheet" href="/habit_tracker/assets/css/dashboard.css">
  <link rel="stylesheet" href="/habit_tracker/assets/css/habits.css">
  <link rel="stylesheet" href="/habit_tracker/assets/css/admin.css">
  <link rel="stylesheet" href="/habit_tracker/assets/css/responsive.css">
</head>
<body>
<div class="app-container">
