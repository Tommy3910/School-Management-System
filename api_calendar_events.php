<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$userId = (int) ($_SESSION['user']['id'] ?? 0);
$role = $_SESSION['user']['role'] ?? '';
$year = (int) ($_GET['year'] ?? date('Y'));
$month = (int) ($_GET['month'] ?? date('n'));

// 1. Malaysia Public Holidays
$publicHolidays = [
    "$year-01-01" => "New Year's Day",
    "$year-02-17" => "Chinese New Year (Day 1)",
    "$year-02-18" => "Chinese New Year (Day 2)",
    "$year-03-21" => "Hari Raya Aidilfitri (Day 1)",
    "$year-03-22" => "Hari Raya Aidilfitri (Day 2)",
    "$year-05-01" => "Labour Day",
    "$year-05-27" => "Hari Raya Haji (Day 1)",
    "$year-05-28" => "Hari Raya Haji (Day 2)",
    "$year-05-31" => "Wesak Day",
    "$year-06-01" => "Agong's Birthday",
    "$year-06-17" => "Awal Muharram",
    "$year-08-25" => "Maulidur Rasul",
    "$year-08-31" => "Merdeka Day",
    "$year-09-16" => "Malaysia Day",
    "$year-11-08" => "Deepavali",
    "$year-11-09" => "Deepavali (Replacement)",
    "$year-12-25" => "Christmas Day",
];

$events = [];

foreach ($publicHolidays as $date => $name) {
    $events[] = [
        'id' => 'hol_' . $date,
        'title' => $name,
        'date' => $date,
        'time' => 'Full Day',
        'type' => 'holiday',
        'badge' => 'Public Holiday',
        'color' => '#10b981', // Emerald green
        'bg' => '#ecfdf5',
        'border' => '#a7f3d0',
        'icon' => 'fas fa-flag',
        'description' => 'Official national public holiday in Malaysia.',
    ];
}

// 2. School Special Events & Exams from DB
if ($conn) {
    try {
        $res = $conn->query("SELECT * FROM calendar_events ORDER BY event_date ASC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $type = $row['event_type'];
                $color = '#8b5cf6'; // Purple default
                $bg = '#f5f3ff';
                $border = '#ddd6fe';
                $icon = 'fas fa-star';
                $badge = 'Special Event';

                if ($type === 'exam') {
                    $color = '#ef4444'; // Red
                    $bg = '#fef2f2';
                    $border = '#fecaca';
                    $icon = 'fas fa-file-signature';
                    $badge = 'Examination';
                } elseif ($type === 'sport') {
                    $color = '#d97706'; // Amber
                    $bg = '#fffbeb';
                    $border = '#fde68a';
                    $icon = 'fas fa-running';
                    $badge = 'Sports Event';
                }

                $events[] = [
                    'id' => 'evt_' . $row['id'],
                    'title' => $row['title'],
                    'date' => $row['event_date'],
                    'time' => $row['event_time'] ?: 'All Day',
                    'type' => $type,
                    'badge' => $badge,
                    'color' => $color,
                    'bg' => $bg,
                    'border' => $border,
                    'icon' => $icon,
                    'description' => $row['description'] ?: 'School campus event.',
                ];
            }
        }
    } catch (Exception $e) {
        // Table may not exist yet
    }
}

// 3. Homework & Deadlines for current student (or all if teacher)
if ($conn && $userId > 0) {
    try {
        $hwSql = ($role === 'student')
            ? "SELECT h.*, u.name as student_name FROM homework h LEFT JOIN users u ON h.student_id=u.id WHERE h.student_id = $userId ORDER BY h.due_date ASC"
            : "SELECT h.*, u.name as student_name FROM homework h LEFT JOIN users u ON h.student_id=u.id ORDER BY h.due_date ASC LIMIT 50";

        $res = $conn->query($hwSql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $isSubmitted = ($row['status'] === 'submitted');
                $color = $isSubmitted ? '#059669' : '#ea580c'; // Emerald or Orange
                $bg = $isSubmitted ? '#f0fdf4' : '#fff7ed';
                $border = $isSubmitted ? '#bbf7d0' : '#fed7aa';
                $icon = $isSubmitted ? 'fas fa-check-circle' : 'fas fa-clock';
                $badge = $row['type'] . ($isSubmitted ? ' (Submitted)' : ' Due');

                $events[] = [
                    'id' => 'hw_' . $row['id'],
                    'title' => ($role === 'teacher' ? ($row['student_name'] . ': ') : '') . $row['title'],
                    'date' => $row['due_date'],
                    'time' => '11:59 PM',
                    'type' => 'homework',
                    'badge' => $badge,
                    'color' => $color,
                    'bg' => $bg,
                    'border' => $border,
                    'icon' => $icon,
                    'description' => 'Assignment deadline: ' . $row['type'] . ' · Status: ' . ucfirst($row['status']),
                    'status' => $row['status'],
                    'hw_id' => (int) $row['id'],
                ];
            }
        }
    } catch (Exception $e) {
        // Handle error gracefully
    }
}

echo json_encode([
    'success' => true,
    'events' => $events,
]);
