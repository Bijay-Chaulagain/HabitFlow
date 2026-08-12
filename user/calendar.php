<?php
/**
 * Monthly Completion Calendar View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';

require_user();

$currentUser = get_logged_in_user();
$userId = $currentUser['id'];

$pdo = getDBConnection();

// Get selected Month and Year (default to current month and year)
$currentMonth = (int)date('n');
$currentYear  = (int)date('Y');

$selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : $currentMonth;
$selectedYear  = isset($_GET['year']) ? (int)$_GET['year'] : $currentYear;

if ($selectedMonth < 1 || $selectedMonth > 12) $selectedMonth = $currentMonth;
if ($selectedYear < 2000 || $selectedYear > 2100) $selectedYear = $currentYear;

// Navigation target month/year calculations
$prevMonth = $selectedMonth - 1;
$prevYear  = $selectedYear;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $selectedMonth + 1;
$nextYear  = $selectedYear;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Calendar Month Grid Parameters
$firstDayTimestamp = strtotime("$selectedYear-$selectedMonth-01");
$daysInMonth       = date('t', $firstDayTimestamp);
$startDayOfWeek    = date('w', $firstDayTimestamp); // 0 (Sun) to 6 (Sat)
$monthName         = date('F Y', $firstDayTimestamp);

$monthStartDate = date('Y-m-01', $firstDayTimestamp);
$monthEndDate   = date('Y-m-t', $firstDayTimestamp);

// Fetch all completion records for user in this month
$stmt = $pdo->prepare("
    SELECT hc.completion_date, h.id AS habit_id, h.name AS habit_name, c.name AS category_name
    FROM habit_completions hc
    JOIN habits h ON hc.habit_id = h.id
    JOIN categories c ON h.category_id = c.id
    WHERE h.user_id = :user_id AND hc.completion_date BETWEEN :start_date AND :end_date
    ORDER BY hc.completion_date ASC, h.name ASC
");

$stmt->execute([
    'user_id'    => $userId,
    'start_date' => $monthStartDate,
    'end_date'   => $monthEndDate
]);

$monthCompletions = $stmt->fetchAll();

// Group completions by completion date string 'Y-m-d'
$completionsByDate = [];
foreach ($monthCompletions as $rec) {
    $d = $rec['completion_date'];
    if (!isset($completionsByDate[$d])) {
        $completionsByDate[$d] = [];
    }
    $completionsByDate[$d][] = $rec;
}

$todayStr = get_today_date();

$pageTitle = 'Monthly Calendar — ' . $monthName;
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="app-main">
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <div class="app-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Habit Calendar</h1>
        <p class="page-subtitle">Visual month-by-month history of your habit completion consistency.</p>
      </div>
      
      <!-- Month Navigation Controls -->
      <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="/habit_tracker/user/calendar.php?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-outline btn-sm">
          &laquo; Prev Month
        </a>
        <a href="/habit_tracker/user/calendar.php?month=<?= $currentMonth ?>&year=<?= $currentYear ?>" class="btn btn-outline btn-sm">
          Today
        </a>
        <a href="/habit_tracker/user/calendar.php?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-outline btn-sm">
          Next Month &raquo;
        </a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="card">
      <div style="text-align: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?= e($monthName) ?></h2>
      </div>

      <!-- Calendar Table Grid -->
      <div class="table-responsive">
        <table class="table" style="border: 1px solid var(--border-color); table-layout: fixed;">
          <thead>
            <tr style="text-align: center;">
              <th style="width: 14.28%; text-align: center;">Sun</th>
              <th style="width: 14.28%; text-align: center;">Mon</th>
              <th style="width: 14.28%; text-align: center;">Tue</th>
              <th style="width: 14.28%; text-align: center;">Wed</th>
              <th style="width: 14.28%; text-align: center;">Thu</th>
              <th style="width: 14.28%; text-align: center;">Fri</th>
              <th style="width: 14.28%; text-align: center;">Sat</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $dayCounter = 1;
            $cellCounter = 0;
            
            while ($dayCounter <= $daysInMonth) {
                echo '<tr>';
                for ($col = 0; $col < 7; $col++) {
                    if ($cellCounter < $startDayOfWeek || $dayCounter > $daysInMonth) {
                        echo '<td style="background-color: var(--bg-main); min-height: 100px; vertical-align: top; padding: 0.5rem;"></td>';
                    } else {
                        $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $dayCounter);
                        $isToday = ($dateStr === $todayStr);
                        $dayCompletions = $completionsByDate[$dateStr] ?? [];
                        $count = count($dayCompletions);
                        
                        $bgColor = $isToday ? 'var(--primary-light)' : 'var(--bg-surface)';
                        $borderStyle = $isToday ? '2px solid var(--primary)' : '1px solid var(--border-color)';
                        
                        echo '<td class="js-calendar-day" style="background-color: ' . $bgColor . '; border: ' . $borderStyle . '; min-height: 100px; height: 110px; vertical-align: top; padding: 0.625rem; position: relative; cursor: pointer;">';
                        
                        echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.375rem;">';
                        echo '<span style="font-weight: 700; font-size: 0.9375rem; color: ' . ($isToday ? 'var(--primary)' : 'var(--text-main)') . ';">' . $dayCounter . '</span>';
                        if ($count > 0) {
                            echo '<span class="badge badge-success" style="font-size: 0.7rem; padding: 0.15rem 0.4rem;">' . $count . ' Done</span>';
                        }
                        echo '</div>';
                        
                        // Small indicator badges for completed habits
                        if ($count > 0) {
                            echo '<div style="display: flex; flex-direction: column; gap: 0.25rem;">';
                            $shown = 0;
                            foreach ($dayCompletions as $h) {
                                if ($shown < 2) {
                                    echo '<div style="font-size: 0.71875rem; background: var(--success-light); color: var(--success); padding: 0.125rem 0.375rem; border-radius: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">✓ ' . e($h['habit_name']) . '</div>';
                                }
                                $shown++;
                            }
                            if ($count > 2) {
                                echo '<div style="font-size: 0.6875rem; color: var(--text-muted);">+' . ($count - 2) . ' more...</div>';
                            }
                            echo '</div>';
                        }
                        
                        echo '</td>';
                        $dayCounter++;
                    }
                    $cellCounter++;
                }
                echo '</tr>';
            }
            ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</main>

<script src="/habit_tracker/assets/js/calendar.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
