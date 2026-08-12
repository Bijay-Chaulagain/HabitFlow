<?php
/**
 * User Dashboard Main View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

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

// 2. Fetch today's completion records for logged-in user
$todayCompletionsStmt = $pdo->prepare("
    SELECT hc.habit_id
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND hc.completion_date = :completion_date AND h.status = 'active'
");
$todayCompletionsStmt->execute([
    'user_id'         => $userId,
    'completion_date' => $today
]);
$todayCompletedHabitIds = $todayCompletionsStmt->fetchAll(PDO::FETCH_COLUMN);

// Calculate Daily Progress Metrics
$totalActiveCount = count($activeHabits);
$completedTodayCount = count($todayCompletedHabitIds);
$progressPercentage = $totalActiveCount > 0 ? round(($completedTodayCount / $totalActiveCount) * 100) : 0;

// 3. Calculate Overall All-Time Stats for User
// Total all-time completions for user
$totalCompletionsStmt = $pdo->prepare("
    SELECT COUNT(hc.id)
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id
");
$totalCompletionsStmt->execute(['user_id' => $userId]);
$totalAllTimeCompletions = (int)$totalCompletionsStmt->fetchColumn();

// Fetch all completion dates across all user habits to calculate overall user streak
$allCompletionsStmt = $pdo->prepare("
    SELECT DISTINCT hc.completion_date
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    WHERE h.user_id = :user_id AND h.status = 'active'
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
    $possibleCompletions = $daysDiff * $totalActiveCount;
    $overallRate = min(100, round(($totalAllTimeCompletions / $possibleCompletions) * 100));
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
        <h1 class="page-title"><?= e($greeting) ?>, <?= e($userName) ?>! 👋</h1>
        <p class="page-subtitle">Here is your daily habit overview for <strong><?= date('l, F j, Y') ?></strong></p>
      </div>
      <div style="display: flex; gap: 0.5rem;">
        <a href="/habit_tracker/user/add-habit.php" class="btn btn-primary">➕ Add Habit</a>
        <a href="/habit_tracker/user/calendar.php" class="btn btn-outline">📅 Calendar</a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <!-- Daily Progress Summary Card -->
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <h2 class="card-title" style="margin-bottom: 0;">Today's Progress</h2>
        <span style="font-size: 1.25rem; font-weight: 800; color: var(--primary);">
          <?= $completedTodayCount ?> / <?= $totalActiveCount ?> Completed (<?= $progressPercentage ?>%)
        </span>
      </div>

      <div class="progress-bar-container" style="height: 14px;">
        <div class="progress-bar-fill" style="width: <?= $progressPercentage ?>%;"></div>
      </div>
      
      <p style="font-size: 0.84375rem; color: var(--text-muted); margin-top: 0.5rem;">
        <?php if ($progressPercentage === 100 && $totalActiveCount > 0): ?>
          🎉 Fantastic! You have completed all your habits for today!
        <?php elseif ($totalActiveCount === 0): ?>
          You don't have any active habits. Add a habit to get started.
        <?php else: ?>
          Keep going! Complete <?= $totalActiveCount - $completedTodayCount ?> more habit<?= ($totalActiveCount - $completedTodayCount) === 1 ? '' : 's' ?> today to hit 100%.
        <?php endif; ?>
      </p>
    </div>

    <!-- Stat Metrics Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon warning">🔥</div>
        <div>
          <div class="stat-value"><?= $currentStreak ?> Day<?= $currentStreak === 1 ? '' : 's' ?></div>
          <div class="stat-label">Current Streak</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon success">🏆</div>
        <div>
          <div class="stat-value"><?= $longestStreak ?> Day<?= $longestStreak === 1 ? '' : 's' ?></div>
          <div class="stat-label">Longest Streak</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon primary">✅</div>
        <div>
          <div class="stat-value"><?= $totalAllTimeCompletions ?></div>
          <div class="stat-label">Total Completions</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon primary">🎯</div>
        <div>
          <div class="stat-value"><?= $totalActiveCount ?></div>
          <div class="stat-label">Active Habits</div>
        </div>
      </div>
    </div>

    <!-- Today's Habits Checklist -->
    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
        <h2 class="card-title" style="margin-bottom: 0;">Today's Habits</h2>
        <a href="/habit_tracker/user/habits.php" style="font-size: 0.875rem; font-weight: 600;">Manage Habits &rarr;</a>
      </div>

      <?php if (empty($activeHabits)): ?>
        <div class="empty-state">
          <div class="empty-state-icon">📝</div>
          <h3 class="empty-state-title">No Active Habits Today</h3>
          <p class="empty-state-desc">You don't have any active habits set up yet. Create your first habit to start building your streak!</p>
          <a href="/habit_tracker/user/add-habit.php" class="btn btn-primary">Create Your First Habit</a>
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
          <?php foreach ($activeHabits as $habit): 
            $isCompleted = in_array($habit['id'], $todayCompletedHabitIds);
          ?>
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); background-color: var(--bg-surface); transition: var(--transition);">
              
              <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="font-size: 1.5rem;">
                  <?= $isCompleted ? '✅' : '⏳' ?>
                </div>
                <div>
                  <div style="font-size: 1.05rem; font-weight: 700; color: <?= $isCompleted ? 'var(--text-muted)' : 'var(--text-main)' ?>; text-decoration: <?= $isCompleted ? 'line-through' : 'none' ?>;">
                    <?= e($habit['name']) ?>
                  </div>
                  <div style="font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.125rem;">
                    Category: <strong><?= e($habit['category_name']) ?></strong> &bull; Frequency: <?= ucfirst(e($habit['frequency'])) ?>
                  </div>
                </div>
              </div>

              <div>
                <?php if ($isCompleted): ?>
                  <a href="/habit_tracker/actions/undo-completion.php?habit_id=<?= $habit['id'] ?>&redirect=/habit_tracker/user/dashboard.php" class="btn btn-success btn-sm btn-complete completed">
                    ✓ Completed (Undo)
                  </a>
                <?php else: ?>
                  <a href="/habit_tracker/actions/complete-habit.php?habit_id=<?= $habit['id'] ?>&redirect=/habit_tracker/user/dashboard.php" class="btn btn-outline btn-sm btn-complete">
                    Mark Complete
                  </a>
                <?php endif; ?>
              </div>

            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>

  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
