<?php
/**
 * User Statistics & Analytics View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$pdo = getDBConnection();
$today = get_today_date();

// 1. Core Summary Counts
$totalHabitsCount = (int)$pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = :user_id")->execute(['user_id' => $userId]) ? $pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = :user_id")->execute(['user_id' => $userId]) : 0;

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = :user_id");
$stmtTotal->execute(['user_id' => $userId]);
$totalHabits = (int)$stmtTotal->fetchColumn();

$stmtActive = $pdo->prepare("SELECT COUNT(*) FROM habits WHERE user_id = :user_id AND status = 'active'");
$stmtActive->execute(['user_id' => $userId]);
$activeHabitsCount = (int)$stmtActive->fetchColumn();

$stmtCompletions = $pdo->prepare("
    SELECT COUNT(hc.id)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id
");
$stmtCompletions->execute(['user_id' => $userId]);
$totalCompletions = (int)$stmtCompletions->fetchColumn();

// 2. Streaks Calculation
$stmtDates = $pdo->prepare("
    SELECT DISTINCT hc.completion_date
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id
    ORDER BY hc.completion_date DESC
");
$stmtDates->execute(['user_id' => $userId]);
$allCompletionDates = $stmtDates->fetchAll(PDO::FETCH_COLUMN);

$streaks = calculate_streaks($allCompletionDates);
$currentStreak = $streaks['current_streak'];
$longestStreak = $streaks['longest_streak'];

// 3. Last 7 Days (Weekly) Breakdown
$weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $dateKey = date('Y-m-d', strtotime("-$i days"));
    $dateLabel = date('D (M j)', strtotime("-$i days"));
    
    $stmtDay = $pdo->prepare("
        SELECT COUNT(hc.id)
        FROM habit_completions hc
        JOIN habits h ON hc.habit_id = h.id
        WHERE h.user_id = :user_id AND hc.completion_date = :cdate
    ");
    $stmtDay->execute(['user_id' => $userId, 'cdate' => $dateKey]);
    $count = (int)$stmtDay->fetchColumn();
    
    $weeklyData[] = [
        'date'  => $dateKey,
        'label' => $dateLabel,
        'count' => $count
    ];
}

$last7DaysTotal = array_sum(array_column($weeklyData, 'count'));

// 4. Last 30 Days (Monthly) Total
$stmtMonth = $pdo->prepare("
    SELECT COUNT(hc.id)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND hc.completion_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
");
$stmtMonth->execute(['user_id' => $userId]);
$last30DaysTotal = (int)$stmtMonth->fetchColumn();

// 5. Best-Performing Habit
$stmtBest = $pdo->prepare("
    SELECT h.id, h.name, c.name AS category_name, COUNT(hc.id) AS completion_count
    FROM habits h
    JOIN categories c ON h.category_id = c.id
    LEFT JOIN habit_completions hc ON h.id = hc.habit_id
    WHERE h.user_id = :user_id
    GROUP BY h.id
    ORDER BY completion_count DESC, h.created_at ASC
    LIMIT 1
");
$stmtBest->execute(['user_id' => $userId]);
$bestHabit = $stmtBest->fetch();

// 6. Habit Completion Breakdown List
$stmtHabitBreakdown = $pdo->prepare("
    SELECT h.id, h.name, h.frequency, c.name AS category_name, COUNT(hc.id) AS completion_count
    FROM habits h
    JOIN categories c ON h.category_id = c.id
    LEFT JOIN habit_completions hc ON h.id = hc.habit_id
    WHERE h.user_id = :user_id AND h.status != 'archived'
    GROUP BY h.id
    ORDER BY completion_count DESC
");
$stmtHabitBreakdown->execute(['user_id' => $userId]);
$habitBreakdownList = $stmtHabitBreakdown->fetchAll();

$pageTitle = 'Statistics';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Habit Statistics & Analytics</h1>
        <p class="page-subtitle">Track your consistency, streak history, and completion performance.</p>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- Summary Metrics -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon warning">🔥</div>
        <div>
          <div class="stat-value"><?= $currentStreak ?> Days</div>
          <div class="stat-label">Current Streak</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon success">🏆</div>
        <div>
          <div class="stat-value"><?= $longestStreak ?> Days</div>
          <div class="stat-label">Longest Streak</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon primary">✅</div>
        <div>
          <div class="stat-value"><?= $totalCompletions ?></div>
          <div class="stat-label">Total Completions</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon primary">🎯</div>
        <div>
          <div class="stat-value"><?= $activeHabitsCount ?> / <?= $totalHabits ?></div>
          <div class="stat-label">Active Habits</div>
        </div>
      </div>
    </div>

    <!-- Highlights Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
      
      <!-- Best Performing Habit Card -->
      <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title">🌟 Best Performing Habit</h2>
        <?php if ($bestHabit && $bestHabit['completion_count'] > 0): ?>
          <div style="padding: 1rem; border-radius: var(--border-radius-sm); background-color: var(--primary-light); border: 1px solid rgba(79, 70, 229, 0.2);">
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">
              <?= e($bestHabit['name']) ?>
            </div>
            <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">
              Category: <strong><?= e($bestHabit['category_name']) ?></strong>
            </div>
            <div style="margin-top: 0.75rem; font-size: 1rem; font-weight: 700; color: var(--text-main);">
              🎉 <?= (int)$bestHabit['completion_count'] ?> Total Completions
            </div>
          </div>
        <?php else: ?>
          <p style="color: var(--text-muted); font-size: 0.9375rem;">
            No completion data available yet. Complete your habits to highlight your top performer!
          </p>
        <?php endif; ?>
      </div>

      <!-- Monthly & Weekly Overview -->
      <div class="card" style="margin-bottom: 0;">
        <h2 class="card-title">📅 Period Summaries</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <div>
              <div style="font-weight: 600;">Last 7 Days</div>
              <div style="font-size: 0.75rem; color: var(--text-muted);">Weekly completions</div>
            </div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--success);"><?= $last7DaysTotal ?></div>
          </div>

          <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background-color: var(--bg-main); border-radius: var(--border-radius-sm);">
            <div>
              <div style="font-weight: 600;">Last 30 Days</div>
              <div style="font-size: 0.75rem; color: var(--text-muted);">Monthly completions</div>
            </div>
            <div style="font-size: 1.25rem; font-weight: 800; color: var(--primary);"><?= $last30DaysTotal ?></div>
          </div>
        </div>
      </div>

    </div>

    <!-- Weekly Breakdown Bar Chart (Last 7 Days) -->
    <div class="card">
      <h2 class="card-title">📊 Last 7 Days Activity</h2>
      
      <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 0.75rem; height: 180px; padding-top: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color);">
        <?php 
        $maxCount = max(1, max(array_column($weeklyData, 'count')));
        foreach ($weeklyData as $day):
          $barHeight = round(($day['count'] / $maxCount) * 120);
        ?>
          <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end;">
            <div style="font-size: 0.75rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">
              <?= $day['count'] > 0 ? $day['count'] : '' ?>
            </div>
            <div style="width: 100%; max-width: 36px; height: <?= max(6, $barHeight) ?>px; background-color: <?= $day['count'] > 0 ? 'var(--primary)' : 'var(--border-color)' ?>; border-radius: 4px 4px 0 0; transition: var(--transition);"></div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; text-align: center;">
              <?= e($day['label']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Per-Habit Completion Performance Table -->
    <div class="card">
      <h2 class="card-title">🎯 Individual Habit Performance</h2>

      <?php if (empty($habitBreakdownList)): ?>
        <p style="color: var(--text-muted);">No habits recorded yet.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Habit Name</th>
                <th>Category</th>
                <th>Frequency</th>
                <th>Total Completions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($habitBreakdownList as $h): ?>
                <tr>
                  <td><strong><?= e($h['name']) ?></strong></td>
                  <td><span class="badge badge-primary"><?= e($h['category_name']) ?></span></td>
                  <td><?= ucfirst(e($h['frequency'])) ?></td>
                  <td><strong><?= (int)$h['completion_count'] ?></strong> completions</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
