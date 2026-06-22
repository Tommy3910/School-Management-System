<?php
require_once 'config.php';
header('Content-Type: application/json');

$userId = (int)($_GET['user_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT
        id,
        description,
        amount,
        status,
        DATE_FORMAT(created_at,'%d %b %Y') AS date
    FROM payments
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();

$payments = [];

while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode([
    "payments" => $payments
]);