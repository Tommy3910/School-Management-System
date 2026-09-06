<?php
require_once 'config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in.']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

$stmt = $conn->prepare("DELETE FROM chatbot_messages WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);