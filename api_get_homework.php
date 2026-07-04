<?php
// api_get_homework.php
// JSON endpoint for the Flutter app — returns this student's homework
// rows from the `homework` table, same data config.php loads into
// $HOMEWORK for the web Homework Status tab.
//
// Usage: ?user_id=1

require_once 'config.php';
header('Content-Type: application/json');

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if (!$userId) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, title, type, due_date, status
     FROM homework
     WHERE student_id = ?
     ORDER BY due_date ASC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$homework = [];
while ($row = $result->fetch_assoc()) {
    $homework[] = [
        'id'     => (int) $row['id'],
        'item'   => $row['title'],
        'type'   => $row['type'],
        'due'    => date('d M Y', strtotime($row['due_date'])),
        'status' => $row['status'],
    ];
}
$stmt->close();

echo json_encode([
    'homework' => $homework,
]);