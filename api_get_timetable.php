<?php
require_once 'config.php';
header('Content-Type: application/json');

$year = (int)($_GET['year'] ?? 0);

if ($year === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Year parameter is required.',
        'timetable' => [],
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT day, p1, p2, p3, p4, p5, p6, p7, sport_day
    FROM timetable
    WHERE year = ?
    ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday')
");

$stmt->bind_param('i', $year);
$stmt->execute();

$result = $stmt->get_result();

$timetable = [];

while ($row = $result->fetch_assoc()) {
    $periods = [];
    for ($i = 1; $i <= 7; $i++) {
        $period_key = 'p' . $i;
        if (!empty($row[$period_key])) {
            $periods[] = $row[$period_key];
        }
    }

    $timetable[] = [
        'day' => $row['day'],
        'periods' => $periods,
        'isSportDay' => (bool) $row['sport_day'],
    ];
}

echo json_encode([
    'success' => true,
    'timetable' => $timetable,
]);
