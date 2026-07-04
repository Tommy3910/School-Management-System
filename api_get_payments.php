<?php
// api_get_payments.php
// JSON endpoint for the Flutter app — returns this student's payment
// records from the `payments` table, same data config.php loads into
// $PAYMENTS (filtered to this user) for the web Payment tab.
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
    "SELECT id, amount, currency, description, status, created_at
     FROM payments
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = [
        'id'          => (int) $row['id'],
        'description' => $row['description'] ?? ($row['currency'] . ' Invoice'),
        'status'      => $row['status'] ?? 'Invoice Sent',
        'date'        => date('d M Y', strtotime($row['created_at'])),
        'amount'      => (float) $row['amount'],
    ];
}
$stmt->close();

echo json_encode([
    'payments' => $payments,
]);