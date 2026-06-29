<?php
// api_get_timetable.php
// JSON endpoint for the Flutter app — returns this student's timetable
// rows from the `timetable` table, same data config.php loads into
// $TIMETABLE for the web Timetable tab. Year group is derived from the
// student's year, same logic as config.php and studentHomePage.php use.

require_once 'config.php';
header('Content-Type: application/json');

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if (!$userId) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

// Look up the student's year directly from the DB (Flutter has no PHP session)
$stmt = $conn->prepare("SELECT year FROM users WHERE id = ? AND role = 'student' LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(['error' => 'Invalid user']);
    exit;
}

$year = (int) $user['year'];
if ($year <= 6) {
    $yearGroup = '1-6';
} elseif ($year <= 9) {
    $yearGroup = '7-9';
} else {
    $yearGroup = '10-11';
}

$stmt2 = $conn->prepare(
    "SELECT day, p1, p2, p3, p4, p5, p6, p7, sport_day
     FROM timetable
     WHERE year = ? AND year_group = ?
     ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday')"
);
$stmt2->bind_param('is', $year, $yearGroup);
$stmt2->execute();
$result = $stmt2->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = [
        'day'       => $row['day'],
        'periods'   => array_values(array_filter([
            $row['p1'], $row['p2'], $row['p3'], $row['p4'], $row['p5'], $row['p6'], $row['p7'],
        ], fn($p) => $p !== null && $p !== '')),
        'sportDay'  => (bool) $row['sport_day'],
    ];
}
$stmt2->close();

echo json_encode([
    'yearGroup' => $yearGroup,
    'year'      => $year,
    'timetable' => $rows,
]);