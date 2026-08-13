<?php
/**
 * User Statistics & Analytics View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$pdo = getDBConnection();
$today = get_today_date();

// 1. Core Summary Counts
// Total repetitions (sum of per-day counts) across all habits
$stmtCompletions = $pdo->prepare("
    SELECT COALESCE(SUM(hc.count), 0)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id
");
$stmtCompletions->execute(['user_id' => $userId]);
$totalRepetitions = (int)$stmtCompletions->fetchColumn();

// Total fully completed habit-days (count reached the daily target)
$stmtFullyCompletedDays = $pdo->prepare("
    SELECT COUNT(*)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND hc.count >= h.target
");
$stmtFullyCompletedDays->execute(['user_id' => $userId]);
$fullyCompletedHabitDays = (int)$stmtFullyCompletedDays->fetchColumn();

// 2. Streaks Calculation
// User-level streak: a day counts only when at least one habit was fully completed (count >= target)
$stmtDates = $pdo->prepare("
    SELECT DISTINCT hc.completion_date
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND hc.count >= h.target
    ORDER BY hc.completion_date DESC
");
$stmtDates->execute(['user_id' => $userId]);
$allCompletionDates = $stmtDates->fetchAll(PDO::FETCH_COLUMN);

$streaks = calculate_streaks($allCompletionDates);
$currentStreak = $streaks['current_streak'];
$longestStreak = $streaks['longest_streak'];

// 3. Completion Rate: share of the last 30 days with at least one fully completed habit
$completionRate = 0;
if (!empty($allCompletionDates)) {
    $monthStart = date('Y-m-d', strtotime('-29 days'));
    $daysActive = 0;
    foreach ($allCompletionDates as $date) {
        if ($date < $monthStart) {
            break; // dates are sorted newest first
        }
        $daysActive++;
    }
    $completionRate = (int)round(($daysActive / 30) * 100);
}

// 4. Last 7 Days (Weekly) Breakdown — number of habits fully completed per day
$weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $dateKey = date('Y-m-d', strtotime("-$i days"));
    $dateLabel = date('D (M j)', strtotime("-$i days"));
    
    $stmtDay = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN hc.count >= h.target THEN 1 ELSE 0 END), 0)
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

// 5. Last 30 Days (Monthly) Total — fully completed habit-days
$stmtMonth = $pdo->prepare("
    SELECT COUNT(*)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND hc.completion_date >= DATE_SUB(CURDATE(), INTERVAL 29 DAY) AND hc.count >= h.target
");
$stmtMonth->execute(['user_id' => $userId]);
$last30DaysTotal = (int)$stmtMonth->fetchColumn();

// 6. Best-Performing Habit (ranked by fully completed habit-days)
$stmtBest = $pdo->prepare("
    SELECT h.id, h.name, h.target, c.name AS category_name,
           COALESCE(SUM(hc.count), 0) AS repetitions,
           COALESCE(SUM(CASE WHEN hc.count >= h.target THEN 1 ELSE 0 END), 0) AS completed_days
    FROM habits h
    JOIN categories c ON h.category_id = c.id
    LEFT JOIN habit_completions hc ON h.id = hc.habit_id
    WHERE h.user_id = :user_id
    GROUP BY h.id
    ORDER BY completed_days DESC, h.created_at ASC
    LIMIT 1
");
$stmtBest->execute(['user_id' => $userId]);
$bestHabit = $stmtBest->fetch();

// 7. Habit Completion Breakdown List — distinguishes repetitions from fully completed habit-days
$stmtHabitBreakdown = $pdo->prepare("
    SELECT h.id, h.name, h.frequency, h.target, c.name AS category_name,
           COALESCE(SUM(hc.count), 0) AS repetitions,
           COALESCE(SUM(CASE WHEN hc.count >= h.target THEN 1 ELSE 0 END), 0) AS completed_days
    FROM habits h
    JOIN categories c ON h.category_id = c.id
    LEFT JOIN habit_completions hc ON h.id = hc.habit_id
    WHERE h.user_id = :user_id AND h.status != 'archived'
    GROUP BY h.id
    ORDER BY completed_days DESC, repetitions DESC
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
        <div class="stat-icon warning"><?= icon('flame', 20) ?></div>
        <div>
          <div class="stat-value"><?= $currentStreak ?> Day<?= $currentStreak === 1 ? '' : 's' ?></div>
          <div class="stat-label">Current Streak</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon success"><?= icon('trophy', 20) ?></div>
        <div>
          <div class="stat-value"><?= $longestStreak ?> Day<?= $longestStreak === 1 ? '' : 's' ?></div>
          <div class="stat-label">Longest Streak</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon primary"><?= icon('check-circle', 20) ?></div>
        <div>
          <div class="stat-value"><?= $totalRepetitions ?></div>
          <div class="stat-label">Total Repetitions</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon primary"><?= icon('trending', 20) ?></div>
        <div>
          <div class="stat-value"><?= $completionRate ?>%</div>
          <div class="stat-label">30-Day Completion Rate</div>
        </div>
      </div>
    </div>

    <!-- Key Insights -->
    <div class="insights-grid">

      <!-- Best Performing Habit -->
      <section class="card">
        <h2 class="card-title">Best Performing Habit</h2>
        <?php if ($bestHabit && $bestHabit['completed_days'] > 0): ?>
          <div class="highlight-item">
            <div class="stat-icon success"><?= icon('trophy', 20) ?></div>
            <div>
              <div class="highlight-name"><?= e($bestHabit['name']) ?></div>
              <div class="highlight-meta">Category: <strong><?= e($bestHabit['category_name']) ?></strong></div>
              <div class="highlight-count"><?= icon('check', 14) ?> <?= (int)$bestHabit['completed_days'] ?> Fully Completed Day<?= (int)$bestHabit['completed_days'] === 1 ? '' : 's' ?> &middot; <?= (int)$bestHabit['repetitions'] ?> Repetition<?= (int)$bestHabit['repetitions'] === 1 ? '' : 's' ?></div>
            </div>
          </div>
        <?php else: ?>
          <p class="text-muted-note">No completion data available yet. Complete your habits to highlight your top performer!</p>
        <?php endif; ?>
      </section>

      <!-- Weekly & Monthly Overview -->
      <section class="card">
        <h2 class="card-title">Period Summaries</h2>
        <div class="period-list">
          <div class="period-row">
            <div class="period-info">
              <div class="period-label">Last 7 Days</div>
              <div class="period-sub">Fully completed habit-days</div>
            </div>
            <div class="period-value is-green"><?= $last7DaysTotal ?></div>
          </div>

          <div class="period-row">
            <div class="period-info">
              <div class="period-label">Last 30 Days</div>
              <div class="period-sub">Fully completed habit-days</div>
            </div>
            <div class="period-value is-primary"><?= $last30DaysTotal ?></div>
          </div>
        </div>
      </section>

    </div>

    <!-- Weekly Breakdown Bar Chart (Last 7 Days) -->
    <section class="card">
      <h2 class="card-title">Last 7 Days Activity</h2>

      <?php
      $maxCount = max(1, max(array_column($weeklyData, 'count')));
      ?>

      <div class="weekly-chart">
        <?php foreach ($weeklyData as $day): ?>
          <div class="weekly-column">
            <span class="weekly-value<?= $day['count'] > 0 ? '' : ' is-zero' ?>"><?= $day['count'] ?></span>
            <div class="weekly-bar<?= $day['count'] > 0 ? '' : ' is-empty' ?>">
              <?php if ($day['count'] > 0): ?>
                <div class="weekly-bar-fill" style="height: <?= round(($day['count'] / $maxCount) * 100) ?>%;"></div>
              <?php endif; ?>
            </div>
            <span class="weekly-day"><?= e($day['label']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- Per-Habit Completion Performance Table -->
    <section class="card">
      <h2 class="card-title">Individual Habit Performance</h2>

      <?php if (empty($habitBreakdownList)): ?>
        <p class="text-muted-note">No habits recorded yet.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Habit Name</th>
                <th>Category</th>
                <th>Frequency</th>
                <th>Target</th>
                <th>Repetitions</th>
                <th>Fully Completed Days</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($habitBreakdownList as $h): ?>
                <tr>
                  <td><strong><?= e($h['name']) ?></strong></td>
                  <td><span class="badge badge-primary"><?= e($h['category_name']) ?></span></td>
                  <td><?= ucfirst(e($h['frequency'])) ?></td>
                  <td><?= (int)$h['target'] ?>x</td>
                  <td><strong><?= (int)$h['repetitions'] ?></strong> repetition<?= (int)$h['repetitions'] === 1 ? '' : 's' ?></td>
                  <td><strong><?= (int)$h['completed_days'] ?></strong> day<?= (int)$h['completed_days'] === 1 ? '' : 's' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
