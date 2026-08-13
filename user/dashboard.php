<?php
/**
 * User Dashboard Main View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];
$userName = $currentUser['name'];

$pdo = getDBConnection();
$today = get_today_date();

// 1. Fetch active habits belonging to logged-in user
$habitsStmt = $pdo->prepare("
    SELECT h.*, c.name AS category_name
    FROM habits h
    JOIN categories c ON h.category_id = c.id
    WHERE h.user_id = :user_id AND h.status = 'active'
    ORDER BY h.created_at DESC
");
$habitsStmt->execute(['user_id' => $userId]);
$activeHabits = $habitsStmt->fetchAll();

// 2. Fetch today's completion counts (habit_id + count) for logged-in user
$todayCompletionsStmt = $pdo->prepare("
    SELECT hc.habit_id, hc.count
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND hc.completion_date = :completion_date AND h.status = 'active'
");
$todayCompletionsStmt->execute([
    'user_id'         => $userId,
    'completion_date' => $today
]);
$todayCounts = [];
foreach ($todayCompletionsStmt->fetchAll() as $row) {
    $todayCounts[(int)$row['habit_id']] = (int)$row['count'];
}

// Calculate Daily Progress Metrics (target-aware)
// Fraction for each active habit = min(count / target, 1); overall = average of fractions.
$totalActiveCount = count($activeHabits);
$totalProgressFraction = 0;
$fullyCompletedTodayCount = 0;
foreach ($activeHabits as $habit) {
    $habitTarget = max(1, (int)$habit['target']);
    $habitCount  = $todayCounts[$habit['id']] ?? 0;
    $totalProgressFraction += min($habitCount / $habitTarget, 1);
    if ($habitCount >= $habitTarget) {
        $fullyCompletedTodayCount++;
    }
}
$progressPercentage = $totalActiveCount > 0 ? (int)round(($totalProgressFraction / $totalActiveCount) * 100) : 0;
$remainingHabitsCount = $totalActiveCount - $fullyCompletedTodayCount;

// 3. Calculate Overall All-Time Stats for User
// Total all-time repetitions (sum of per-day counts) for user
$totalRepetitionsStmt = $pdo->prepare("
    SELECT COALESCE(SUM(hc.count), 0)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id
");
$totalRepetitionsStmt->execute(['user_id' => $userId]);
$totalAllTimeRepetitions = (int)$totalRepetitionsStmt->fetchColumn();

// Total all-time fully completed habit-days (count reached the daily target)
$fullyCompletedDaysStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND hc.count >= h.target
");
$fullyCompletedDaysStmt->execute(['user_id' => $userId]);
$fullyCompletedHabitDays = (int)$fullyCompletedDaysStmt->fetchColumn();

// Fetch all completion dates across all user habits to calculate overall user streak
// A day only counts once at least one habit was fully completed that day (count >= target)
$allCompletionsStmt = $pdo->prepare("
    SELECT DISTINCT hc.completion_date
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND h.status = 'active' AND hc.count >= h.target
    ORDER BY hc.completion_date DESC
");
$allCompletionsStmt->execute(['user_id' => $userId]);
$allCompletionDates = $allCompletionsStmt->fetchAll(PDO::FETCH_COLUMN);

$streakData = calculate_streaks($allCompletionDates);
$currentStreak = $streakData['current_streak'];
$longestStreak = $streakData['longest_streak'];

// Overall Completion Rate calculation
// Days elapsed since earliest habit start date
$startDateStmt = $pdo->prepare("SELECT MIN(start_date) FROM habits WHERE user_id = :user_id AND status != 'archived'");
$startDateStmt->execute(['user_id' => $userId]);
$earliestStartDate = $startDateStmt->fetchColumn();

$overallRate = 0;
if ($earliestStartDate && $totalActiveCount > 0) {
    $daysDiff = max(1, (int)(new DateTime($today))->diff(new DateTime($earliestStartDate))->format('%a') + 1);
    $possibleHabitDays = $daysDiff * $totalActiveCount;
    $overallRate = min(100, round(($fullyCompletedHabitDays / $possibleHabitDays) * 100));
}

// Determine Greeting based on hour of day
$hour = (int)date('H');
if ($hour < 12) {
    $greeting = "Good Morning";
} else if ($hour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">

    <!-- Greeting & Quick Actions Bar -->
    <div class="page-header">
      <div>
        <h1 class="page-title"><?= e($greeting) ?>, <?= e($userName) ?>!</h1>
        <p class="page-subtitle">Here is your daily habit overview for <strong><?= date('l, F j, Y') ?></strong></p>
      </div>
      <div class="page-actions">
        <a href="/habit_tracker/user/add-habit.php" class="btn btn-primary"><?= icon('plus') ?> Add Habit</a>
        <a href="/habit_tracker/user/calendar.php" class="btn btn-outline"><?= icon('calendar') ?> Calendar</a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- Daily Progress Summary -->
    <section class="card<?= ($progressPercentage === 100 && $totalActiveCount > 0) ? ' is-complete' : '' ?>">
      <div class="progress-card-head">
        <h2 class="card-title">Today's Progress</h2>
        <span class="progress-count">
          <?= $fullyCompletedTodayCount ?> / <?= $totalActiveCount ?> fully completed (<?= $progressPercentage ?>%)
        </span>
      </div>

      <div class="progress-bar-container progress-lg">
        <div class="progress-bar-fill" style="width: <?= $progressPercentage ?>%;"></div>
      </div>

      <p class="progress-note">
        <?php if ($progressPercentage === 100 && $totalActiveCount > 0): ?>
          Fantastic! You have completed all your habits for today.
        <?php elseif ($totalActiveCount === 0): ?>
          You don't have any active habits. Add a habit to get started.
        <?php else: ?>
          Keep going! Fully complete <?= $remainingHabitsCount ?> more habit<?= $remainingHabitsCount === 1 ? '' : 's' ?> today to hit 100%.
        <?php endif; ?>
      </p>
    </section>

    <!-- Today's Habits Checklist -->
    <section class="card">
      <div class="card-head">
        <h2 class="card-title">Today's Habits</h2>
        <a href="/habit_tracker/user/habits.php" class="card-link">Manage Habits <?= icon('arrow-right', 14) ?></a>
      </div>

      <?php if (empty($activeHabits)): ?>
        <div class="empty-state">
          <div class="empty-state-icon"><?= icon('target', 32) ?></div>
          <h3 class="empty-state-title">No Active Habits Today</h3>
          <p class="empty-state-desc">You don't have any active habits set up yet. Create your first habit to start building your streak!</p>
          <a href="/habit_tracker/user/add-habit.php" class="btn btn-primary">Create Your First Habit</a>
        </div>
      <?php else: ?>
        <ul class="habit-today">
          <?php foreach ($activeHabits as $habit):
            $habitTarget = max(1, (int)$habit['target']);
            $habitCount  = $todayCounts[$habit['id']] ?? 0;
            $isFullyCompleted = $habitCount >= $habitTarget;
            $isPartiallyCompleted = $habitCount > 0 && !$isFullyCompleted;
          ?>
            <li class="habit-row<?= $isFullyCompleted ? ' is-completed' : '' ?>">
              <?php if ($isFullyCompleted): ?>
                <a href="/habit_tracker/actions/undo-completion.php?habit_id=<?= $habit['id'] ?>&redirect=/habit_tracker/user/dashboard.php" class="habit-check is-checked" title="Undo one completion"><?= icon('check', 14) ?></a>
              <?php else: ?>
                <a href="/habit_tracker/actions/complete-habit.php?habit_id=<?= $habit['id'] ?>&redirect=/habit_tracker/user/dashboard.php" class="habit-check" title="Log one completion"><?= icon('check', 14) ?></a>
              <?php endif; ?>

              <div class="habit-main">
                <div class="habit-row-name"><?= e($habit['name']) ?></div>
                <div class="habit-row-meta">
                  <span class="badge badge-secondary"><?= e($habit['category_name']) ?></span>
                  <span class="habit-meta-frequency">Frequency: <?= ucfirst(e($habit['frequency'])) ?></span>
                  <span class="badge <?= $isFullyCompleted ? 'badge-success' : ($isPartiallyCompleted ? 'badge-warning' : 'badge-secondary') ?>" title="Today's progress"><?= $habitCount ?>/<?= $habitTarget ?></span>
                </div>
              </div>

              <div class="habit-extra">
                <?php if ($isFullyCompleted): ?>
                  <span class="badge badge-success"><?= icon('check', 12) ?> Completed</span>
                  <a href="/habit_tracker/actions/undo-completion.php?habit_id=<?= $habit['id'] ?>&redirect=/habit_tracker/user/dashboard.php" class="btn-undo" title="Undo one completion">Undo</a>
                <?php elseif ($isPartiallyCompleted): ?>
                  <span class="badge badge-warning">Partially completed</span>
                  <a href="/habit_tracker/actions/undo-completion.php?habit_id=<?= $habit['id'] ?>&redirect=/habit_tracker/user/dashboard.php" class="btn-undo" title="Undo one completion">Undo</a>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    </section>

    <!-- Streak & Secondary Statistics -->
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
          <div class="stat-value"><?= $totalAllTimeRepetitions ?></div>
          <div class="stat-label">Total Repetitions</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon primary"><?= icon('chart', 20) ?></div>
        <div>
          <div class="stat-value"><?= $overallRate ?>%</div>
          <div class="stat-label">Completion Rate</div>
        </div>
      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
