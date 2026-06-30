<?php
// api_get_grades.php
// JSON endpoint for the Flutter app — returns this student's grades
// from the `grades` table joined with `subjects`, same query
// studentHomePage.php uses for the web Grades tab (?tab=grades).
//
// Accepts an optional term param, e.g. ?user_id=1&term=Term 1
// Defaults to "Term 1" same as the web version.

require_once 'config.php';
header('Content-Type: application/json');

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if (!$userId) {
    echo json_encode(['error' => 'Missing user_id']);
    exit;
}

$term = $_GET['term'] ?? 'Term 1';

$stmt = $conn->prepare(
    "SELECT g.marks, g.max_marks, g.grade, g.term, s.name AS subject_name
     FROM grades g
     JOIN subjects s ON g.subject_id = s.id
     WHERE g.student_id = ? AND g.term = ?
     ORDER BY s.name"
);
$stmt->bind_param('is', $userId, $term);
$stmt->execute();
$result = $stmt->get_result();

$grades = [];
$totalMarks = 0;
$totalMax = 0;
while ($row = $result->fetch_assoc()) {
    $marks = (float) $row['marks'];
    $max   = (int) $row['max_marks'];
    $totalMarks += $marks;
    $totalMax   += $max;
    $grades[] = [
        'subject'  => $row['subject_name'],
        'marks'    => $marks,
        'maxMarks' => $max,
        'grade'    => $row['grade'],
        'term'     => $row['term'],
    ];
}
$stmt->close();

$overallPct = $totalMax > 0 ? round(($totalMarks / $totalMax) * 100, 1) : 0;

echo json_encode([
    'term'       => $term,
    'grades'     => $grades,
    'overallPct' => $overallPct,
]);