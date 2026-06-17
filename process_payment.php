<?php
require_once 'config.php';
requireAuth('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    redirect('studentHomePage.php?tab=payment');

$pid = (int) ($_POST['payment_id'] ?? 0);
$userId = $_SESSION['user']['id'] ?? null;

if ($pid <= 0) {
    flash('toast', 'Invalid payment.');
    redirect('studentHomePage.php?tab=payment');
}

// Verify ownership
$res = $conn->query("SELECT * FROM payments WHERE id=$pid LIMIT 1");
if (!$res || !($p = $res->fetch_assoc())) {
    flash('toast', 'Invoice not found.');
    redirect('studentHomePage.php?tab=payment');
}
if ((int) $p['user_id'] !== (int) $userId) {
    flash('toast', 'Not authorized.');
    redirect('studentHomePage.php?tab=payment');
}

// Update as paid (simulated)
$now = date('Y-m-d H:i:s');
$conn->query("UPDATE payments SET status='Paid', paid_at='$now' WHERE id=$pid");

// Send a notification to the user
sendNotification($conn, $userId, 'Payment Received', 'We received your payment for invoice #' . $pid . '. Thank you.');

// Redirect to receipt page
redirect('success.php?id=' . $pid);
