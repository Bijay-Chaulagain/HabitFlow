<?php
/**
 * Monthly Completion Calendar View
 * Habit Tracker Web Application
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';

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
    SELECT hc.completion_date, hc.count AS completion_count, h.target AS habit_target, h.id AS habit_id, h.name AS habit_name, c.name AS category_name
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
      <div class="page-actions">
        <a href="/habit_tracker/user/calendar.php?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-outline btn-sm">
          <?= icon('arrow-left', 16) ?> Prev Month
        </a>
        <a href="/habit_tracker/user/calendar.php?month=<?= $currentMonth ?>&year=<?= $currentYear ?>" class="btn btn-outline btn-sm">
          Today
        </a>
        <a href="/habit_tracker/user/calendar.php?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-outline btn-sm">
          Next Month <?= icon('arrow-right', 16) ?>
        </a>
      </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="card calendar-card">
      <h2 class="calendar-month"><?= e($monthName) ?></h2>

      <!-- Calendar Table Grid -->
      <div class="table-responsive">
        <table class="table calendar-table">
          <thead class="calendar-weekdays">
            <tr>
              <th>Sun</th>
              <th>Mon</th>
              <th>Tue</th>
              <th>Wed</th>
              <th>Thu</th>
              <th>Fri</th>
              <th>Sat</th>
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
                        echo '<td class="calendar-day is-empty"></td>';
                    } else {
                        $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $dayCounter);
                        $isToday = ($dateStr === $todayStr);
                        $dayCompletions = $completionsByDate[$dateStr] ?? [];

                        // Target-aware day classification:
                        //   count >= target = fully completed; 0 < count < target = partial; no row = incomplete.
                        $dayFullyCompleted = 0;
                        $dayPartially = 0;
                        foreach ($dayCompletions as $h) {
                            if ((int)$h['completion_count'] >= max(1, (int)$h['habit_target'])) {
                                $dayFullyCompleted++;
                            } else {
                                $dayPartially++;
                            }
                        }
                        $count = count($dayCompletions);

                        $cellClass = 'calendar-day js-calendar-day'
                            . ($isToday ? ' is-today' : '')
                            . ($count > 0 ? ' has-completions' : '');

                        echo '<td class="' . $cellClass . '">';

                        echo '<div class="calendar-day-head">';
                        echo '<span class="calendar-day-number">' . $dayCounter . '</span>';
                        if ($dayFullyCompleted > 0) {
                            echo '<span class="badge badge-success calendar-day-count">' . $dayFullyCompleted . ' Done</span>';
                        } elseif ($dayPartially > 0) {
                            echo '<span class="badge badge-warning calendar-day-count">' . $dayPartially . ' Partial</span>';
                        }
                        echo '</div>';

                        // Small indicator chips for habit completions (partial vs fully completed)
                        if ($count > 0) {
                            $shown = 0;
                            foreach ($dayCompletions as $h) {
                                if ($shown < 2) {
                                    $isFull = (int)$h['completion_count'] >= max(1, (int)$h['habit_target']);
                                    $stateClass = $isFull ? '' : ' is-partial';
                                    echo '<div class="calendar-day-item' . $stateClass . '">'
                                        . icon('check', 10)
                                        . '<span class="calendar-day-name">' . e($h['habit_name']) . ' ' . (int)$h['completion_count'] . '/' . (int)$h['habit_target'] . '</span>'
                                        . '</div>';
                                }
                                $shown++;
                            }
                            if ($count > 2) {
                                echo '<div class="calendar-day-more">+' . ($count - 2) . ' more</div>';
                            }
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
