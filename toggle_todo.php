<?php
require_once 'config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorized.']);
    exit;
}

$hwId = (int) ($_POST['hw_id'] ?? 0);
$userId = (int) $_SESSION['user']['id'];

if ($hwId <= 0) {
    echo json_encode(['error' => 'Invalid item.']);
    exit;
}

// Only update if it actually belongs to this student — prevents
// someone ticking off another student's homework by guessing an id
$stmt = $conn->prepare("UPDATE homework SET status='submitted' WHERE id = ? AND student_id = ?");
$stmt->bind_param('ii', $hwId, $userId);
$stmt->execute();
$success = $stmt->affected_rows > 0;
$stmt->close();

echo json_encode(['success' => $success]);