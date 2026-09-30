<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$notifId = (int) ($_POST['id'] ?? 0);
$userId = (int) $_SESSION['user']['id'];

if ($notifId > 0 && $conn) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $notifId, $userId);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false]);
